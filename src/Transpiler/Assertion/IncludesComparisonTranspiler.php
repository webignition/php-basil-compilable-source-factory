<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Transpiler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;

class IncludesComparisonTranspiler extends AbstractComparisonAssertionTranspiler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new IncludesComparisonTranspiler(
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

        return in_array($model->getComparison(), [AssertionComparison::INCLUDES, AssertionComparison::EXCLUDES]);
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getComparison(), [AssertionComparison::INCLUDES, AssertionComparison::EXCLUDES])) {
            throw new NonTranspilableModelException($model);
        }

        return $this->doTranspile($model);
    }

    protected function getAssertionTemplate(ComparisonAssertionInterface $assertion): string
    {
        return AssertionComparison::INCLUDES === $assertion->getComparison()
            ? AssertionCallFactory::ASSERT_STRING_CONTAINS_STRING_TEMPLATE
            : AssertionCallFactory::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE;
    }
}
