<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;

class IsComparisonHandler extends AbstractComparisonAssertionHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new IsComparisonHandler(
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

        return in_array($model->getComparison(), [AssertionComparison::IS, AssertionComparison::IS_NOT]);
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

        if (!in_array($model->getComparison(), [AssertionComparison::IS, AssertionComparison::IS_NOT])) {
            throw new NonTranspilableModelException($model);
        }
        return $this->doTranspile($model);
    }

    protected function getAssertionTemplate(ComparisonAssertionInterface $assertion): string
    {
        return AssertionComparison::IS === $assertion->getComparison()
            ? AssertionCallFactory::ASSERT_EQUALS_TEMPLATE
            : AssertionCallFactory::ASSERT_NOT_EQUALS_TEMPLATE;
    }
}
