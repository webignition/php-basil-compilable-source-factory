<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Value;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\AbstractDelegatingTranspiler;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueTranspiler extends AbstractDelegatingTranspiler implements DelegatorInterface, HandlerInterface
{
    public static function createTranspiler(): ScalarValueTranspiler
    {
        return new ScalarValueTranspiler([
            BrowserPropertyTranspiler::createTranspiler(),
            EnvironmentParameterValueTranspiler::createTranspiler(),
            LiteralValueTranspiler::createTranspiler(),
            PagePropertyTranspiler::createTranspiler(),
        ]);
    }

    public function handles(object $model): bool
    {
        if ($model instanceof ValueInterface) {
            return null !== $this->findHandler($model);
        }

        return false;
    }
}
