<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Step\StepInterface;

class StepHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new StepHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof StepInterface;
    }

    public function createSource(object $model): SourceInterface
    {
        if (!$model instanceof StepInterface) {
            throw new UnsupportedModelException($model);
        }

        return new LineList([
            new Statement('')
        ]);
    }
}
