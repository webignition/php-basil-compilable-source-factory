<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Value\LiteralValueInterface;

class LiteralValueHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new LiteralValueHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof LiteralValueInterface;
    }

    public function createSource(object $model): SourceInterface
    {
        if ($this->handles($model)) {
            return new Statement((string) $model);
        }

        throw new UnsupportedModelException($model);
    }
}
