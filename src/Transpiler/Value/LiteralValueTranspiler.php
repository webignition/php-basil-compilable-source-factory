<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Value\LiteralValueInterface;

class LiteralValueTranspiler implements HandlerInterface
{
    public static function createFactory(): LiteralValueTranspiler
    {
        return new LiteralValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof LiteralValueInterface;
    }

    public function createSource(object $model): SourceInterface
    {
        if ($this->handles($model)) {
            return (new Source())->withStatements([
                (string) $model,
            ]);
        }

        throw new NonTranspilableModelException($model);
    }
}
