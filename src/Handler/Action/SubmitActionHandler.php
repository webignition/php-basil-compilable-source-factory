<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilModel\Action\ActionTypes;

class SubmitActionHandler extends AbstractInteractionActionHandler
{
    public static function createHandler(): SubmitActionHandler
    {
        return new SubmitActionHandler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierHandler::createHandler()
        );
    }

    protected function getHandledActionType(): string
    {
        return ActionTypes::SUBMIT;
    }

    protected function getElementActionMethod(): string
    {
        return 'submit';
    }
}
