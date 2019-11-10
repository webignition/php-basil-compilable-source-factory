<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\BlockInterface;

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

    public static function createHandler(): HandlerInterface
    {
        return new AssertionHandler(
            ExistenceComparisonHandler::createHandler(),
            ComparisonAssertionHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        if ($this->existenceComparisonHandler->handles($model)) {
            return true;
        }

        if ($this->comparisonAssertionHandler->handles($model)) {
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
     */
    public function handle(object $model): BlockInterface
    {
        if ($this->existenceComparisonHandler->handles($model)) {
            return $this->existenceComparisonHandler->handle($model);
        }

        if ($this->comparisonAssertionHandler->handles($model)) {
            return $this->comparisonAssertionHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }
}
