<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Value;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\AbstractDelegatingHandler;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueTranspiler extends AbstractDelegatingHandler implements DelegatorInterface, HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new ScalarValueTranspiler([
            BrowserPropertyHandler::createHandler(),
            EnvironmentValueHandler::createHandler(),
            LiteralValueHandler::createHandler(),
            PagePropertyHandler::createHandler(),
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
