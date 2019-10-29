<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Test\TestInterface;

class TestHandler implements HandlerInterface
{
    private $stepHandler;
    public function __construct(HandlerInterface $stepHandler)
    {
        $this->stepHandler = $stepHandler;
    }

    public static function createHandler(): HandlerInterface
    {
        return new TestHandler(
            StepHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof TestInterface;
    }

    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof TestInterface) {
            throw new UnsupportedModelException($model);
        }

        return new LineList([]);
    }
}
