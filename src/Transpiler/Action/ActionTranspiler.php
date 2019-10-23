<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\AbstractDelegatingTranspiler;
use webignition\BasilModel\Action\ActionInterface;

class ActionTranspiler extends AbstractDelegatingTranspiler implements DelegatorInterface, HandlerInterface
{
    public static function createTranspiler(): ActionTranspiler
    {
        return new ActionTranspiler(
            [
                WaitActionTranspiler::createTranspiler(),
                WaitForActionTranspiler::createTranspiler(),
                BrowserOperationActionTranspiler::createTranspiler(),
                ClickActionTranspiler::createTranspiler(),
                SubmitActionTranspiler::createTranspiler(),
                SetActionTranspiler::createTranspiler(),
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
