<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;

class AssertionHandler implements HandlerInterface
{
    private $existenceComparisonHandler;
    private $comparisonAssertionHandler;

    public function __construct(
        ExistenceComparisonHandler $existenceComparisonHandler,
        ComparisonAssertionHandler $comparisonAssertionHandler
    ) {
        $this->existenceComparisonHandler = $existenceComparisonHandler;
        $this->comparisonAssertionHandler = $comparisonAssertionHandler;
    }

    public static function createHandler(): AssertionHandler
    {
        return new AssertionHandler(
            ExistenceComparisonHandler::createHandler(),
            ComparisonAssertionHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        if ($this->isExistenceAssertion($model)) {
            return true;
        }

        if ($this->isComparisonAssertion($model)) {
            return true;
        }

        return false;
    }

    /**
     * @param object $model
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(object $model): BlockInterface
    {
        if ($this->isComparisonAssertion($model)) {
            return $this->comparisonAssertionHandler->handle($model);
        }

        if ($this->isExistenceAssertion($model)) {
            return $this->existenceComparisonHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }

    private function isComparisonAssertion(object $model): bool
    {
        if (!$model instanceof ComparisonAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [
            AssertionComparison::INCLUDES,
            AssertionComparison::EXCLUDES,
            AssertionComparison::IS,
            AssertionComparison::IS_NOT,
            AssertionComparison::MATCHES,
        ]);
    }

    private function isExistenceAssertion(object $model): bool
    {
        if (!$model instanceof ExaminationAssertionInterface) {
            return false;
        }

        return in_array($model->getComparison(), [AssertionComparison::EXISTS, AssertionComparison::NOT_EXISTS]);
    }

}
