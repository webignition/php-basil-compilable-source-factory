<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Action\ActionInterface;

class BrowserOperationActionHandler
{
    public static function createHandler(): BrowserOperationActionHandler
    {
        return new BrowserOperationActionHandler();
    }

    public function handle(ActionInterface $action): CodeBlockInterface
    {
        return new CodeBlock([
            new AssignmentStatement(
                VariablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                    $action->getType()
                )
            ),
        ]);
    }
}
