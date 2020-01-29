<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BaseBasilTestCase\Statement as BasilTestStatement;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Line\StatementInterface;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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

    public function __construct(
        ActionHandler $actionHandler,
        AssertionHandler $assertionHandler,
        DomIdentifierExistenceHandler $domIdentifierExistenceHandler,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->actionHandler = $actionHandler;
        $this->assertionHandler = $assertionHandler;
        $this->domIdentifierExistenceHandler = $domIdentifierExistenceHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createHandler(): StepHandler
    {
        return new StepHandler(
            ActionHandler::createHandler(),
            AssertionHandler::createHandler(),
            DomIdentifierExistenceHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            SingleQuotedStringEscaper::create()
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
                    $block->addLinesFromBlock($this->createActionDerivedAssertions($action));
                } catch (UnsupportedContentException $unsupportedIdentifierException) {
                    throw new UnsupportedStatementException($action, $unsupportedIdentifierException);
                }

                $block->addLinesFromBlock($this->createStatementBlock($action, $this->actionHandler->handle($action)));
            }

            foreach ($step->getAssertions() as $assertion) {
                if (!$this->isExistenceAssertion($assertion)) {
                    try {
                        $block->addLinesFromBlock($this->createAssertionDerivedAssertions($assertion));
                    } catch (UnsupportedContentException $unsupportedIdentifierException) {
                        throw new UnsupportedStatementException($assertion, $unsupportedIdentifierException);
                    }
                }

                $block->addLinesFromBlock(
                    $this->createStatementBlock($assertion, $this->assertionHandler->handle($assertion))
                );
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

        $elementExistsBlock = $this->domIdentifierExistenceHandler->createForElement($domIdentifier);

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

        $elementExistsBlock = $this->domIdentifierExistenceHandler->createForCollection($domIdentifier);

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

        $currentStatementStatement = $this->createCurrentStatementStatement($statement);

        $block->addLine(new Comment($statementCommentContent));
        $block->addLine($currentStatementStatement);

        if ($source instanceof CodeBlockInterface) {
            foreach ($source->getLines() as $sourceLine) {
                $block->addLine($sourceLine);
            }
        }

        $block->addLine(new EmptyLine());

        return $block;
    }

    private function createCurrentStatementStatement(StatementModelInterface $statement): StatementInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $phpUnitPlaceholder = $variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);

        $classDependencies = new ClassDependencyCollection([
            new ClassDependency(BasilTestStatement::class)
        ]);

        $createMethod = $statement instanceof ActionInterface ? 'createAction' : 'createAssertion';

        return new Statement(
            sprintf(
                '%s->currentStatement = Statement::%s(\'%s\')',
                $phpUnitPlaceholder,
                $createMethod,
                $this->singleQuotedStringEscaper->escape($statement->getSource())
            ),
            (new Metadata())
                ->withVariableDependencies($variableDependencies)
                ->withClassDependencies($classDependencies)
        );
    }

    /**
     * @param ActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    private function createActionDerivedAssertions(ActionInterface $action): CodeBlockInterface
    {
        $block = new CodeBlock();

        if ($action instanceof InteractionActionInterface && !$action instanceof InputActionInterface) {
            $block->addLinesFromBlock(
                $this->createDerivedElementExistenceBlock($action->getIdentifier(), $action)
            );
        }

        if ($action instanceof InputActionInterface) {
            $block->addLinesFromBlock(
                $this->createDerivedCollectionExistenceBlock($action->getIdentifier(), $action)
            );

            $value = $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $block->addLinesFromBlock(
                    $this->createDerivedCollectionExistenceBlock($value, $action)
                );
            }
        }

        if ($action instanceof WaitActionInterface) {
            $duration = $action->getDuration();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
                $block->addLinesFromBlock(
                    $this->createDerivedCollectionExistenceBlock($duration, $action)
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
    private function createAssertionDerivedAssertions(AssertionInterface $assertion): CodeBlockInterface
    {
        $block = new CodeBlock();

        $identifier = $assertion->getIdentifier();

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $block->addLinesFromBlock(
                $this->createDerivedCollectionExistenceBlock($identifier, $assertion)
            );
        }

        if ($assertion instanceof ComparisonAssertionInterface) {
            $value = $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $block->addLinesFromBlock(
                    $this->createDerivedCollectionExistenceBlock($value, $assertion)
                );
            }
        }

        return $block;
    }
}
