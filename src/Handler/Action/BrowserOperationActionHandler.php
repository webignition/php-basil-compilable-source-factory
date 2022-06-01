<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Action\ActionInterface;

class BrowserOperationActionHandler
{
    public static function createHandler(): BrowserOperationActionHandler
    {
        return new BrowserOperationActionHandler();
    }

    public function handle(ActionInterface $action): BodyInterface
    {
        return Body::createForSingleAssignmentStatement(
            new VariableDependency(VariableNames::PANTHER_CRAWLER),
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                $action->getType()
            )
        );
    }
}
