<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

abstract class AbstractComparisonAssertionTranspiler implements HandlerInterface
{
    protected $assertionCallFactory;
    private $variableAssignmentFactory;
    private $scalarValueTranspiler;
    private $namedDomIdentifierHandler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        HandlerInterface $scalarValueTranspiler,
        HandlerInterface $namedDomIdentifierHandler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueTranspiler = $scalarValueTranspiler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
    }

    abstract protected function getAssertionTemplate(ComparisonAssertionInterface $assertion): string;

    /**
     * @param ComparisonAssertionInterface $assertion
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    protected function doTranspile(ComparisonAssertionInterface $assertion): SourceInterface
    {
        $examinedValuePlaceholder = new VariablePlaceholder(VariableNames::EXAMINED_VALUE);
        $expectedValuePlaceholder = new VariablePlaceholder(VariableNames::EXPECTED_VALUE);

        $examinedValue = $assertion->getExaminedValue();
        $expectedValue = $assertion->getExpectedValue();

        if ($examinedValue instanceof DomIdentifierValueInterface) {
            $examinedValueAccessor = $this->namedDomIdentifierHandler->createSource(
                new NamedDomIdentifierValue($examinedValue, $examinedValuePlaceholder)
            );

            $examinedValueAccessor->mutateStatement(3, function ($statement) use ($examinedValuePlaceholder) {
                return str_replace((string) $examinedValuePlaceholder . ' = ', '', $statement);
            });
        } else {
            $examinedValueAccessor = $this->scalarValueTranspiler->createSource($examinedValue);
        }

        if ($expectedValue instanceof DomIdentifierValueInterface) {
            $expectedValueAccessor = $this->namedDomIdentifierHandler->createSource(
                new NamedDomIdentifierValue($expectedValue, $expectedValuePlaceholder)
            );

            $expectedValueAccessor->mutateStatement(3, function ($statement) use ($expectedValuePlaceholder) {
                return str_replace((string) $expectedValuePlaceholder . ' = ', '', $statement);
            });
        } else {
            $expectedValueAccessor = $this->scalarValueTranspiler->createSource($expectedValue);
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
            $this->getAssertionTemplate($assertion)
        );
    }
}
