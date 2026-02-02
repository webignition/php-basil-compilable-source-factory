<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Step;

use SmartAssert\DomIdentifier\ElementIdentifier;
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

        if ($action->isInteraction() || $action->isInput()) {
            $assertions = $assertions->append($this->createForStatementAndAncestors(
                $this->createDomIdentifier((string) $action->getIdentifier()),
                $action,
            ));
        }

        if ($action->isInput() || $action->isWait()) {
            $value = (string) $action->getValue();
            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $assertions = $assertions->append($this->createForStatementAndAncestors(
                    $this->createDomIdentifier($value),
                    $action,
                ));
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
                $assertions = $assertions->append($this->createForStatementAncestorsOnly(
                    $this->createDomIdentifier($identifier),
                    $assertion,
                ));
            }

            if (is_string($identifier) && $this->identifierTypeAnalyser->isAttributeIdentifier($identifier)) {
                $attributeDomIdentifier = $this->createDomIdentifier($identifier);
                $elementDomIdentifier = ElementIdentifier::fromAttributeIdentifier($attributeDomIdentifier);

                $assertions = $assertions->append(
                    $this->createForCollection(
                        $assertion,
                        [
                            $elementDomIdentifier,
                        ]
                    ),
                );
            }
        } else {
            if (is_string($identifier) && $this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
                $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
                if (null === $domIdentifier) {
                    throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
                }

                $assertions = $assertions->append($this->createForStatementAndAncestors($domIdentifier, $assertion));
            }
        }

        if ($assertion->isComparison()) {
            $value = (string) $assertion->getValue();

            if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($value)) {
                $valueDomIdentifier = $this->domIdentifierFactory->createFromIdentifierString($value);
                if (null === $valueDomIdentifier) {
                    throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $value);
                }

                $assertions = $assertions->append($this->createForStatementAndAncestors(
                    $valueDomIdentifier,
                    $assertion
                ));
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

    private function createForStatementAndAncestors(
        ElementIdentifierInterface $domIdentifier,
        StatementModelInterface $statement
    ): AssertionCollectionInterface {
        $newAssertionIdentifiers = $domIdentifier->getScope();
        $newAssertionIdentifiers[] = $domIdentifier;

        return $this->createForCollection(
            $statement,
            $newAssertionIdentifiers,
        );
    }

    private function createForStatementAncestorsOnly(
        ElementIdentifierInterface $domIdentifier,
        StatementModelInterface $statement
    ): AssertionCollectionInterface {
        $newAssertionIdentifiers = $domIdentifier->getScope();

        return $this->createForCollection($statement, $newAssertionIdentifiers);
    }

    /**
     * @param ElementIdentifierInterface[] $elementIdentifiers
     */
    private function createForCollection(
        StatementModelInterface $statement,
        array $elementIdentifiers,
    ): AssertionCollectionInterface {
        $assertions = [];

        foreach ($elementIdentifiers as $elementIdentifier) {
            $assertions[] = new DerivedValueOperationAssertion($statement, (string) $elementIdentifier, 'exists');
        }

        return new AssertionCollection($assertions);
    }

    /**
     * @throws UnsupportedContentException
     */
    private function createDomIdentifier(string $identifier): ElementIdentifierInterface
    {
        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        return $domIdentifier;
    }
}
