<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Action\ActionInterface;

class BrowserOperationActionHandler
{
    public static function createHandler(): BrowserOperationActionHandler
    {
        return new BrowserOperationActionHandler();
    }

    public function handle(ActionInterface $action): BodyInterface
    {
        return new Body([
            new AssignmentStatement(
                new VariableDependency(VariableNames::PANTHER_CRAWLER),
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PANTHER_CLIENT),
                    $action->getType()
                )
            ),
        ]);
    }
}
