<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InputActionInterface;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\BasilModels\Action\WaitActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\BasilModels\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Assertion\UniqueAssertionCollection;
use webignition\BasilModels\StatementInterface as StatementModelInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DerivedAssertionFactory
{
    private $domIdentifierFactory;
    private $identifierTypeAnalyser;

    public function __construct(
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function createFactory(): self
    {
        return new DerivedAssertionFactory(
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create()
        );
    }

    /**
     * @param ActionInterface $action
     *
     * @return UniqueAssertionCollection
     *
     * @throws UnsupportedContentException
     */
    public function createForAction(ActionInterface $action): UniqueAssertionCollection
    {
        $assertions = new UniqueAssertionCollection();

        if ($action instanceof InteractionActionInterface && !$action instanceof InputActionInterface) {
            $assertions = $assertions->merge($this->createForStatementAndAncestors($action->getIdentifier(), $action));
        }

        if ($action instanceof InputActionInterface) {
            $assertions = $assertions->merge(
                $this->createForStatementAndAncestors($action->getIdentifier(), $action)
            );

            $value = $action->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($value, $action));
            }
        }

        if ($action instanceof WaitActionInterface) {
            $duration = $action->getDuration();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($duration)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($duration, $action));
            }
        }

        return $assertions;
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return UniqueAssertionCollection
     *
     * @throws UnsupportedContentException
     */
    public function createForAssertion(AssertionInterface $assertion): UniqueAssertionCollection
    {
        $assertions = new UniqueAssertionCollection();

        $isExistenceAssertion = in_array($assertion->getComparison(), ['exists', 'not-exists']);
        $identifier = $assertion->getIdentifier();

        if ($isExistenceAssertion) {
            if ($this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)) {
                $assertions = $assertions->merge($this->createForStatementAncestorsOnly($identifier, $assertion));
            }
        } else {
            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($identifier, $assertion));
            }
        }

        if ($assertion instanceof ComparisonAssertionInterface) {
            $value = $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->merge($this->createForStatementAndAncestors($value, $assertion));
            }
        }

        return $assertions;
    }

    /**
     * @param string $identifier
     * @param StatementModelInterface $statement
     *
     * @return UniqueAssertionCollection
     *
     * @throws UnsupportedContentException
     */
    private function createForStatementAndAncestors(
        string $identifier,
        StatementModelInterface $statement
    ): UniqueAssertionCollection {
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
     * @param string $identifier
     * @param StatementModelInterface $statement
     *
     * @throws UnsupportedContentException
     *
     * @return UniqueAssertionCollection
     */
    private function createForStatementAncestorsOnly(
        string $identifier,
        StatementModelInterface $statement
    ): UniqueAssertionCollection {
        return $this->createForCollection(
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
     * @return UniqueAssertionCollection
     *
     * @throws UnsupportedContentException
     */
    private function createForCollection(
        string $identifier,
        StatementModelInterface $action,
        callable $elementHierarchyCreator
    ): UniqueAssertionCollection {
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $elementHierarchy = $elementHierarchyCreator($domIdentifier);

        $assertions = new UniqueAssertionCollection();

        foreach ($elementHierarchy as $elementIdentifier) {
            $assertions->add(new DerivedValueOperationAssertion($action, (string) $elementIdentifier, 'exists'));
        }

        return $assertions;
    }
}
