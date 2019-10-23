<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierHandler;
use webignition\BasilModel\Action\ActionTypes;

class ClickActionTranspiler extends AbstractInteractionActionTranspiler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new ClickActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    protected function getHandledActionType(): string
    {
        return ActionTypes::CLICK;
    }

    protected function getElementActionMethod(): string
    {
        return 'click';
    }
}
