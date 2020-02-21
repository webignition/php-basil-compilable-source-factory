<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BaseBasilTestCase\Statement as BasilTestStatement;
use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InputActionInterface;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\BasilModels\Action\WaitActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\BasilModels\Assertion\DerivedAssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\StatementInterface as StatementModelInterface;
use webignition\BasilModels\Step\StepInterface;

class StepHandler
{
    private $actionHandler;
    private $assertionHandler;
    private $domIdentifierExistenceHandler;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;
    private $singleQuotedStringEscaper;
    private $assertionFailureMessageFactory;

    public function __construct(
        ActionHandler $actionHandler,
        AssertionHandler $assertionHandler,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        AssertionFailureMessageFactory $assertionFailureMessageFactory
    ) {
        $this->actionHandler = $actionHandler;
        $this->assertionHandler = $assertionHandler;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->assertionFailureMessageFactory = $assertionFailureMessageFactory;
    }

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler(),
            DomIdentifierExistenceHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            SingleQuotedStringEscaper::create(),
            AssertionFailureMessageFactory::createFactory()
        );
    }

    /**
     * @param StepInterface $step
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedStepException
     */
    public function handle(StepInterface $step): CodeBlockInterface
    {
        $block = new CodeBlock([]);

        try {
            foreach ($step->getActions() as $action) {
                try {
                    $derivedActionAssertions = $this->createDerivedAssertionsForAction($action);
                    $block->addLines($derivedActionAssertions->getLines());
                } catch (UnsupportedContentException $unsupportedIdentifierException) {
                    throw new UnsupportedStatementException($action, $unsupportedIdentifierException);
                }

                $statementBlock = $this->createStatementBlock($action, $this->actionHandler->handle($action));
                $block->addLines($statementBlock->getLines());
            }

            foreach ($step->getAssertions() as $assertion) {
                if (!$this->isExistenceAssertion($assertion)) {
                    try {
                        $block->addLines(
                            $this->createDerivedAssertionsForAssertion($assertion)->getLines()
                        );
                    } catch (UnsupportedContentException $unsupportedIdentifierException) {
                        throw new UnsupportedStatementException($assertion, $unsupportedIdentifierException);
                    }
                }

                $statementBlock = $this->createStatementBlock($assertion, $this->assertionHandler->handle($assertion));
                $block->addLines($statementBlock->getLines());
            }
        } catch (UnsupportedStatementException $unsupportedStatementException) {
            throw new UnsupportedStepException($step, $unsupportedStatementException);
        }

        return $block;
    }

    private function isExistenceAssertion(AssertionInterface $assertion): bool
    {
        return in_array($assertion->getComparison(), ['exists', 'not-exists']);
    }

    /**
     * @param string $identifier
     * @param StatementModelInterface $action
     *
     * @throws UnsupportedContentException
     *
     * @return CodeBlockInterface
     */
    private function createDerivedElementExistenceBlock(
        string $identifier,
        StatementModelInterface $action
    ): CodeBlockInterface {
        $elementExistsAssertion = new DerivedElementExistsAssertion($action, $identifier);

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementExistsBlock = $this->domIdentifierExistenceHandler->createForElement(
            $domIdentifier,
            $this->assertionFailureMessageFactory->createForAssertion($elementExistsAssertion)
        );

        return $this->createStatementBlock($elementExistsAssertion, $elementExistsBlock);
    }

    /**
     * @param string $identifier
     * @param StatementModelInterface $action
     *
     * @throws UnsupportedContentException
     *
     * @return CodeBlockInterface
     */
    private function createDerivedCollectionExistenceBlock(
        string $identifier,
        StatementModelInterface $action
    ): CodeBlockInterface {
        $elementExistsAssertion = new DerivedElementExistsAssertion($action, $identifier);

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementExistsBlock = $this->domIdentifierExistenceHandler->createForCollection(
            $domIdentifier,
            $this->assertionFailureMessageFactory->createForAssertion($elementExistsAssertion)
        );

        return $this->createStatementBlock($elementExistsAssertion, $elementExistsBlock);
    }

    private function createStatementBlock(
        StatementModelInterface $statement,
        CodeBlockInterface $source
    ): CodeBlockInterface {
        $block = new CodeBlock();

        $statementCommentContent = $statement->getSource();

        if ($statement instanceof DerivedAssertionInterface) {
            $statementCommentContent .= ' <- ' . $statement->getSourceStatement()->getSource();
        }

        $statementPlaceholder = VariablePlaceholder::createExport(VariableNames::STATEMENT);

        $statementAssignment = new AssignmentStatement(
            $statementPlaceholder,
            new StaticObjectMethodInvocation(
                new StaticObject(BasilTestStatement::class),
                $statement instanceof ActionInterface ? 'createAction' : 'createAssertion',
                [
                    new LiteralExpression(sprintf(
                        '\'%s\'',
                        $this->singleQuotedStringEscaper->escape($statement->getSource())
                    ))
                ]
            )
        );

        $currentStatementStatement = new AssignmentStatement(
            new ObjectPropertyAccessExpression(
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'currentStatement'
            ),
            $statementPlaceholder
        );

        $block->addLine(new SingleLineComment($statementCommentContent));
        $block->addLine($statementAssignment);
        $block->addLine($currentStatementStatement);

        if ($source instanceof CodeBlockInterface) {
            foreach ($source->getLines() as $sourceLine) {
                $block->addLine($sourceLine);
            }
        }

        $block->addLine($this->createAddToCompletedStatementsStatement($statementPlaceholder));
        $block->addLine(new EmptyLine());

        return $block;
    }

    private function createAddToCompletedStatementsStatement(
        VariablePlaceholder $statementPlaceholder
    ): StatementInterface {
        return new AssignmentStatement(
            new ObjectPropertyAccessExpression(
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'completedStatements[]'
            ),
            $statementPlaceholder
        );
    }

    /**
     * @param ActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function createDerivedAssertionsForAction(ActionInterface $action): CodeBlockInterface
    {
        $block = new CodeBlock();

        if ($action instanceof InteractionActionInterface && !$action instanceof InputActionInterface) {
            $derivedElementExistenceBlock = $this->createDerivedElementExistenceBlock(
                $action->getIdentifier(),
                $action
            );

            $block->addLines($derivedElementExistenceBlock->getLines());
        }

        if ($action instanceof InputActionInterface) {
            $derivedCollectionExistenceBlock = $this->createDerivedCollectionExistenceBlock(
                $action->getIdentifier(),
                $action
            );

            $block->addLines($derivedCollectionExistenceBlock->getLines());

            $value = $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $block->addLines(
                    $this->createDerivedCollectionExistenceBlock($value, $action)->getLines()
                );
            }
        }

        if ($action instanceof WaitActionInterface) {
            $duration = $action->getDuration();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
                $block->addLines(
                    $this->createDerivedCollectionExistenceBlock($duration, $action)->getLines()
                );
            }
        }

        return $block;
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function createDerivedAssertionsForAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $block = new CodeBlock();

        $identifier = $assertion->getIdentifier();

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $block->addLines(
                $this->createDerivedCollectionExistenceBlock($identifier, $assertion)->getLines()
            );
        }

        if ($assertion instanceof ComparisonAssertionInterface) {
            $value = $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $block->addLines(
                    $this->createDerivedCollectionExistenceBlock($value, $assertion)->getLines()
                );
            }
        }

        return $block;
    }
}
