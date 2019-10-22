<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilModel\Action\ActionTypes;

class ClickActionTranspiler extends AbstractInteractionActionTranspiler implements
    HandlerInterface,
    TranspilerInterface
{
    public static function createTranspiler(): ClickActionTranspiler
    {
        return new ClickActionTranspiler(
            VariableAssignmentFactory::createFactory(),
            NamedDomIdentifierTranspiler::createTranspiler()
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
