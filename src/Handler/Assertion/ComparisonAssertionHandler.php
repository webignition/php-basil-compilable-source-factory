<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\MutableBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

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

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    public static function createHandler(): ComparisonAssertionHandler
    {
        return new ComparisonAssertionHandler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory(),
            new ScalarValueHandler(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(ComparisonAssertionInterface $assertion): CodeBlockInterface
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getExaminedValue();
        $expectedValue = $assertion->getExpectedValue();

        if ($examinedValue instanceof DomIdentifierValueInterface) {
            $examinedValueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($examinedValue, $examinedValuePlaceholder)
            );

            if ($examinedValueAccessor instanceof MutableBlockInterface) {
                $examinedValueAccessor->mutateLastStatement(function (string $content) use ($examinedValuePlaceholder) {
                    return str_replace((string) $examinedValuePlaceholder . ' = ', '', $content);
                });
            }
        } else {
            $examinedValueAccessor = $this->scalarValueHandler->handle($examinedValue);
        }

        if ($expectedValue instanceof DomIdentifierValueInterface) {
            $expectedValueAccessor = $this->namedDomIdentifierHandler->handle(
                new NamedDomIdentifierValue($expectedValue, $expectedValuePlaceholder)
            );

            if ($expectedValueAccessor instanceof MutableBlockInterface) {
                $expectedValueAccessor->mutateLastStatement(function (string $content) use ($expectedValuePlaceholder) {
                    return str_replace((string) $expectedValuePlaceholder . ' = ', '', $content);
                });
            }
        } else {
            $expectedValueAccessor = $this->scalarValueHandler->handle($expectedValue);
        }

        $examinedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $examinedValueAccessor,
            $examinedValuePlaceholder
        );

        $expectedValueAssignment = $this->variableAssignmentFactory->createForValueAccessor(
            $expectedValueAccessor,
            $expectedValuePlaceholder
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
