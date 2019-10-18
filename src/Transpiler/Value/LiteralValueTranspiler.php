<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Value\LiteralValueInterface;

class LiteralValueTranspiler implements HandlerInterface, TranspilerInterface
{
    public static function createTranspiler(): LiteralValueTranspiler
    {
        return new LiteralValueTranspiler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof LiteralValueInterface;
    }

    public function transpile(object $model): SourceInterface
    {
        if ($this->handles($model)) {
            return (new Source())->withStatements([
                (string) $model,
            ]);
        }

        throw new NonTranspilableModelException($model);
    }
}
