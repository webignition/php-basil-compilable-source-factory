<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\AbstractDelegatingHandler;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandler extends AbstractDelegatingHandler implements DelegatorInterface, HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new ScalarValueHandler([
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
