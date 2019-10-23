<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilModel\Action\ActionTypes;

class ClickActionHandler extends AbstractInteractionActionHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new ClickActionHandler(
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
