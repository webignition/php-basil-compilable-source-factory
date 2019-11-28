<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
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
use webignition\BasilModel\Assertion\AssertionComparison;

class ComparisonAssertionHandler
{
    private const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
        AssertionComparison::INCLUDES => AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE,
        AssertionComparison::EXCLUDES => AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE,
        AssertionComparison::IS => AssertionCallFactory::ASSERT_EQUALS_TEMPLATE,
        AssertionComparison::IS_NOT => AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE,
        AssertionComparison::MATCHES => AssertionCallFactory::ASSERT_MATCHES_TEMPLATE,
    ];

    protected $assertionCallFactory;
    private $variableAssignmentFactory;
    private $scalarValueHandler;
    private $namedDomIdentifierHandler;
    private $accessorDefaultValueFactory;
    private $domIdentifierFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        DomIdentifierFactory $domIdentifierFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
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
     * @throws UnknownIdentifierException
     * @throws UnsupportedValueException
     */
    public function handle(AssertionInterface $assertion): CodeBlockInterface
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getIdentifier();
        $expectedValue = $assertion->getValue();

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
