<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Handler\AbstractDelegatingHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandler extends AbstractDelegatingHandler
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
