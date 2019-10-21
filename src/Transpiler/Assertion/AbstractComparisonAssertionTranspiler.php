<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\Value\ScalarValueTranspiler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Value\DomIdentifierValueInterface;

abstract class AbstractComparisonAssertionTranspiler implements HandlerInterface, TranspilerInterface
{
    protected $assertionCallFactory;
    private $variableAssignmentFactory;
    private $scalarValueTranspiler;
    private $namedDomIdentifierTranspiler;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        VariableAssignmentFactory $variableAssignmentFactory,
        ScalarValueTranspiler $scalarValueTranspiler,
        NamedDomIdentifierTranspiler $namedDomIdentifierTranspiler
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->variableAssignmentFactory = $variableAssignmentFactory;
        $this->scalarValueTranspiler = $scalarValueTranspiler;
        $this->namedDomIdentifierTranspiler = $namedDomIdentifierTranspiler;
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
            $examinedValueAccessor = $this->namedDomIdentifierTranspiler->transpile(
                new NamedDomIdentifierValue($examinedValue, $examinedValuePlaceholder)
            );
        } else {
            $examinedValueAccessor = $this->scalarValueTranspiler->transpile($examinedValue);
        }

        if ($expectedValue instanceof DomIdentifierValueInterface) {
            $expectedValueAccessor = $this->namedDomIdentifierTranspiler->transpile(
                new NamedDomIdentifierValue($expectedValue, $expectedValuePlaceholder)
            );
        } else {
            $expectedValueAccessor = $this->scalarValueTranspiler->transpile($expectedValue);
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
