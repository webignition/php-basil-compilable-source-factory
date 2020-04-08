<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InputActionInterface;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\BasilModels\Action\WaitActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\StatementInterface as StatementModelInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DerivedAssertionFactory
{
    private $assertionHandler;
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;
    private $statementBlockFactory;

    public function __construct(
        AssertionHandler $assertionHandler,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        StatementBlockFactory $statementBlockFactory
    ) {
        $this->assertionHandler = $assertionHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->statementBlockFactory = $statementBlockFactory;
    }

    public static function createFactory(): self
    {
        return new DerivedAssertionFactory(
            AssertionHandler::createHandler(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            StatementBlockFactory::createFactory()
        );
    }

    /**
     * @param ActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     * @throws UnsupportedStatementException
     */
    public function createForAction(ActionInterface $action): CodeBlockInterface
    {
        $block = new CodeBlock();

        if ($action instanceof InteractionActionInterface && !$action instanceof InputActionInterface) {
            $block->addBlock($this->createForStatementAndAncestors($action->getIdentifier(), $action));
        }

        if ($action instanceof InputActionInterface) {
            $block->addBlock($this->createForStatementAndAncestors($action->getIdentifier(), $action));

            $value = $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $block->addBlock($this->createForStatementAndAncestors($value, $action));
            }
        }

        if ($action instanceof WaitActionInterface) {
            $duration = $action->getDuration();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
                $block->addBlock($this->createForStatementAndAncestors($duration, $action));
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
     * @throws UnsupportedStatementException
     */
    public function createForAssertion(AssertionInterface $assertion): CodeBlockInterface
    {
        $block = new CodeBlock();

        $isExistenceAssertion = in_array($assertion->getComparison(), ['exists', 'not-exists']);
        $identifier = $assertion->getIdentifier();

        if ($isExistenceAssertion) {
            if ($this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)) {
                $block->addBlock($this->createForStatementAncestorsOnly($identifier, $assertion));
            }
        } else {
            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
                $block->addBlock($this->createForStatementAndAncestors($identifier, $assertion));
            }
        }

        if ($assertion instanceof ComparisonAssertionInterface) {
            $value = $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $block->addBlock($this->createForStatementAndAncestors($value, $assertion));
            }
        }

        return $block;
    }

    /**
     * @param string $identifier
     * @param StatementModelInterface $statement
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     * @throws UnsupportedStatementException
     */
    private function createForStatementAndAncestors(
        string $identifier,
        StatementModelInterface $statement
    ): CodeBlockInterface {
        return $this->createForCollectionExistence(
            $identifier,
            $statement,
            function (ElementIdentifierInterface $domIdentifier): array {
                $elementHierarchy = $domIdentifier->getScope();
                $elementHierarchy[] = $domIdentifier;

                return $elementHierarchy;
            }
        );
    }

    /**
     * @param string $identifier
     * @param StatementModelInterface $statement
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedStatementException
     * @throws UnsupportedContentException
     */
    private function createForStatementAncestorsOnly(
        string $identifier,
        StatementModelInterface $statement
    ): CodeBlockInterface {
        return $this->createForCollectionExistence(
            $identifier,
            $statement,
            function (ElementIdentifierInterface $domIdentifier): array {
                return $domIdentifier->getScope();
            }
        );
    }

    /**
     * @param string $identifier
     * @param StatementModelInterface $action
     * @param callable $elementHierarchyCreator
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     * @throws UnsupportedStatementException
     */
    private function createForCollectionExistence(
        string $identifier,
        StatementModelInterface $action,
        callable $elementHierarchyCreator
    ): CodeBlockInterface {
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementHierarchy = $elementHierarchyCreator($domIdentifier);

        $codeBlock = new CodeBlock();

        foreach ($elementHierarchy as $elementIdentifier) {
            $elementExistsAssertion = new DerivedElementExistsAssertion($action, (string) $elementIdentifier);

            $codeBlock->addBlock($this->statementBlockFactory->create($elementExistsAssertion));
            $codeBlock->addBlock(
                $this->assertionHandler->handle($elementExistsAssertion)
            );
        }

        return $codeBlock;
    }
}
