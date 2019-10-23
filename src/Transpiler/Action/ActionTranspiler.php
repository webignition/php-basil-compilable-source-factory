<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\AbstractDelegatingHandler;
use webignition\BasilModel\Action\ActionInterface;

class ActionTranspiler extends AbstractDelegatingHandler implements DelegatorInterface, HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new ActionTranspiler(
            [
                WaitActionTranspiler::createHandler(),
                WaitForActionTranspiler::createHandler(),
                BrowserOperationActionTranspiler::createHandler(),
                ClickActionTranspiler::createHandler(),
                SubmitActionTranspiler::createHandler(),
                SetActionTranspiler::createHandler(),
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
