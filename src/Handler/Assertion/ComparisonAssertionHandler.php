<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilDataStructure\AssertionInterface;

class ComparisonAssertionHandler extends AbstractAssertionHandler
{
    private const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
        'excludes' => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
        'is' => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
        'is-not' => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
        'matches' => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
    ];

    private $variableAssignmentFactory;
    private $accessorDefaultValueFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        parent::__construct(
            $assertionCallFactory,
            $scalarValueHandler,
            $namedDomIdentifierHandler,
            $domIdentifierFactory
        );

        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
    }

    public static function createHandler(): ComparisonAssertionHandler
    {
        return new ComparisonAssertionHandler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler(),
            AccessorDefaultValueFactory::createFactory(),
            DomIdentifierFactory::createFactory()
        );
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedIdentifierException
     * @throws UnsupportedValueException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getIdentifier();
        $expectedValue = $assertion->getValue();

        if (null === $examinedValue || null === $expectedValue) {
            throw new UnsupportedValueException(null);
        }

        if (
            IdentifierTypeFinder::isDomIdentifier($examinedValue) ||
            IdentifierTypeFinder::isDescendantDomIdentifier($examinedValue)
        ) {
            $examinedValueDomIdentifier = $this->domIdentifierFactory->create($examinedValue);

            $examinedValueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($examinedValueDomIdentifier, $examinedValuePlaceholder)
            );

            $examinedValueAccessor->mutateLastStatement(function (string $content) use ($examinedValuePlaceholder) {
                return str_replace((string) $examinedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $examinedValueAccessor = $this->scalarValueHandler->handle($examinedValue);
        }

        if (
            IdentifierTypeFinder::isDomIdentifier($expectedValue) ||
            IdentifierTypeFinder::isDescendantDomIdentifier($expectedValue)
        ) {
            $expectedValueDomIdentifier = $this->domIdentifierFactory->create($expectedValue);

            $expectedValueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue(
                    $expectedValueDomIdentifier,
                    $expectedValuePlaceholder
                )
            );

            $expectedValueAccessor->mutateLastStatement(function (string $content) use ($expectedValuePlaceholder) {
                return str_replace((string) $expectedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $expectedValueAccessor = $this->scalarValueHandler->handle($expectedValue);
        }

        $examinedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $examinedValueAccessor,
            $examinedValuePlaceholder,
            $this->accessorDefaultValueFactory->create($examinedValue)
        );

        $expectedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $expectedValueAccessor,
            $expectedValuePlaceholder,
            $this->accessorDefaultValueFactory->create($expectedValue)
        );

        return $this->assertionCallFactory->createValueComparisonAssertionCall(
            $expectedValueAssignment,
            $examinedValueAssignment,
            $expectedValuePlaceholder,
            $examinedValuePlaceholder,
            self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$assertion->getComparison()]
        );
    }
}
