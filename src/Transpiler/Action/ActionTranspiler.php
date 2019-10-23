<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\AbstractDelegatingTranspiler;
use webignition\BasilModel\Action\ActionInterface;

class ActionTranspiler extends AbstractDelegatingTranspiler implements DelegatorInterface, HandlerInterface
{
    public static function createFactory(): ActionTranspiler
    {
        return new ActionTranspiler(
            [
                WaitActionTranspiler::createFactory(),
                WaitForActionTranspiler::createFactory(),
                BrowserOperationActionTranspiler::createFactory(),
                ClickActionTranspiler::createFactory(),
                SubmitActionTranspiler::createFactory(),
                SetActionTranspiler::createFactory(),
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
