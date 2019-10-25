<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

class ComparisonAssertionHandler implements HandlerInterface
{
    const HANDLED_COMPARISONS = [
        AssertionComparison::INCLUDES,
        AssertionComparison::EXCLUDES,
        AssertionComparison::IS,
        AssertionComparison::IS_NOT,
        AssertionComparison::MATCHES,
    ];

    const COMPARISON_TO_ASSERTION_TEMPLATE_MAP = [
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
        HandlerInterface $scalarValueHandler,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new ComparisonAssertionHandler(
            AssertionCallFactory::createFactory(),
            VariableAssignmentFactory::createFactory(),
            ScalarValueHandler::createHandler(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), self::HANDLED_COMPARISONS);
    }

    public function createStatementList(object $model): SourceInterface
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), self::HANDLED_COMPARISONS)) {
            throw new NonTranspilableModelException($model);
        }

        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $model->getExaminedValue();
        $expectedValue = $model->getExpectedValue();

        if ($examinedValue instanceof DomIdentifierValueInterface) {
            $examinedValueAccessor = $this->namedDomIdentifierHandler->createStatementList(
                new NamedDomIdentifierValue($examinedValue, $examinedValuePlaceholder)
            );

            $examinedValueAccessor->mutateLastStatement(function (string $content) use ($examinedValuePlaceholder) {
                return str_replace((string) $examinedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $examinedValueAccessor = $this->scalarValueHandler->createStatementList($examinedValue);
        }

        if ($expectedValue instanceof DomIdentifierValueInterface) {
            $expectedValueAccessor = $this->namedDomIdentifierHandler->createStatementList(
                new NamedDomIdentifierValue($expectedValue, $expectedValuePlaceholder)
            );

            $expectedValueAccessor->mutateLastStatement(function (string $content) use ($expectedValuePlaceholder) {
                return str_replace((string) $expectedValuePlaceholder . ' = ', '', $content);
            });
        } else {
            $expectedValueAccessor = $this->scalarValueHandler->createStatementList($expectedValue);
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
            self::COMPARISON_TO_ASSERTION_TEMPLATE_MAP[$model->getComparison()]
        );
    }
}
