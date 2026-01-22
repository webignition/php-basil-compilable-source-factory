<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use SmartAssert\DomIdentifier\ElementIdentifierInterface;
use SmartAssert\DomIdentifier\Factory as DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollectionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\Statement\StatementInterface as StatementModelInterface;

class DerivedAssertionFactory
{
    public function __construct(
        private DomIdentifierFactory $domIdentifierFactory,
        private IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {}

    public static function createFactory(): self
    {
        return new DerivedAssertionFactory(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function createForAction(ActionInterface $action): AssertionCollectionInterface
    {
        $assertions = new AssertionCollection([]);

        if ($action->isInteraction()) {
            $assertions = $assertions->append($this->createForStatementAndAncestors(
                (string) $action->getIdentifier(),
                $action
            ));
        }

        if ($action->isInput()) {
            $assertions = $assertions->append(
                $this->createForStatementAndAncestors((string) $action->getIdentifier(), $action)
            );

            $value = (string) $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->append($this->createForStatementAndAncestors($value, $action));
            }
        }

        if ($action->isWait()) {
            $duration = (string) $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
                $assertions = $assertions->append($this->createForStatementAndAncestors($duration, $action));
            }
        }

        return $assertions;
    }

    /**
     * @throws UnsupportedContentException
     */
    public function createForAssertion(AssertionInterface $assertion): AssertionCollectionInterface
    {
        return new AssertionCollection([])
            ->append(
                $this->createExistenceAssertionsForElementalComponents($assertion)
            )
            ->append(
                $this->createRegexValidationAssertions($assertion)
            )
        ;
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createExistenceAssertionsForElementalComponents(
        AssertionInterface $assertion
    ): AssertionCollectionInterface {
        $assertions = new AssertionCollection([]);

        $isExistenceAssertion = in_array($assertion->getOperator(), ['exists', 'not-exists']);
        $identifier = $assertion->getIdentifier();

        if ($isExistenceAssertion) {
            if (is_string($identifier) && $this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)) {
                $assertions = $assertions->append($this->createForStatementAncestorsOnly($identifier, $assertion));
            }
        } else {
            if (is_string($identifier) && $this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
                $assertions = $assertions->append($this->createForStatementAndAncestors($identifier, $assertion));
            }
        }

        if ($assertion->isComparison()) {
            $value = (string) $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->append($this->createForStatementAndAncestors($value, $assertion));
            }
        }

        return $assertions;
    }

    private function createRegexValidationAssertions(AssertionInterface $assertion): AssertionCollectionInterface
    {
        if (!$assertion->isComparison() || 'matches' !== $assertion->getOperator()) {
            return new AssertionCollection([]);
        }

        return new AssertionCollection([])->append(
            new AssertionCollection([
                new DerivedValueOperationAssertion($assertion, (string) $assertion->getValue(), 'is-regexp'),
            ])
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createForStatementAndAncestors(
        string $identifier,
        StatementModelInterface $statement
    ): AssertionCollectionInterface {
        return $this->createForCollection(
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
     * @throws UnsupportedContentException
     */
    private function createForStatementAncestorsOnly(
        string $identifier,
        StatementModelInterface $statement
    ): AssertionCollectionInterface {
        return $this->createForCollection(
            $identifier,
            $statement,
            function (ElementIdentifierInterface $domIdentifier): array {
                return $domIdentifier->getScope();
            }
        );
    }

    /**
     * @param callable(ElementIdentifierInterface): ElementIdentifierInterface[] $elementHierarchyCreator
     *
     * @throws UnsupportedContentException
     */
    private function createForCollection(
        string $identifier,
        StatementModelInterface $action,
        callable $elementHierarchyCreator
    ): AssertionCollectionInterface {
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $assertions = [];

        $elementHierarchy = $elementHierarchyCreator($domIdentifier);
        foreach ($elementHierarchy as $elementIdentifier) {
            $assertions[] = new DerivedValueOperationAssertion($action, (string) $elementIdentifier, 'exists');
        }

        return new AssertionCollection($assertions);
    }
}
