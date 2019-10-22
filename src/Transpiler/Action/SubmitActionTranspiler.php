<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilModel\Action\ActionTypes;

class SubmitActionTranspiler extends AbstractInteractionActionTranspiler implements
    HandlerInterface,
    TranspilerInterface
{
    public static function createTranspiler(): SubmitActionTranspiler
    {
        return new SubmitActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierTranspiler::createTranspiler()
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
