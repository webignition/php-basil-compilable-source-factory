<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\AbstractDelegatingHandler;
use webignition\BasilModel\Action\ActionInterface;

class ActionHandler extends AbstractDelegatingHandler implements DelegatorInterface, HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new ActionHandler(
            [
                WaitActionHandler::createHandler(),
                WaitForActionHandler::createHandler(),
                BrowserOperationActionHandler::createHandler(),
                ClickActionHandler::createHandler(),
                SubmitActionHandler::createHandler(),
                SetActionHandler::createHandler(),
            ]
        );
    }

    public function handles(object $model): bool
    {
        if ($model instanceof ActionInterface) {
            return null !== $this->findHandler($model);
        }

        return false;
    }
}