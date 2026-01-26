<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

class BrowserOperationActionHandler implements StatementHandlerInterface
{
    public function __construct(
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createHandler(): BrowserOperationActionHandler
    {
        return new BrowserOperationActionHandler(
            PhpUnitCallFactory::createFactory(),
        );
    }

    public function handle(StatementInterface $statement): ?StatementHandlerComponents
    {
        if (!$statement instanceof ActionInterface) {
            return null;
        }

        if (
            !in_array($statement->getType(), ['back', 'forward', 'reload'])) {
            return null;
        }

        return new StatementHandlerComponents(
            new Body([
                new Statement(
                    new AssignmentExpression(
                        new VariableDependency(VariableName::PANTHER_CRAWLER),
                        new ObjectMethodInvocation(
                            object: new VariableDependency(VariableName::PANTHER_CLIENT),
                            methodName: $statement->getType(),
                            arguments: new MethodArguments(),
                            mightThrow: false,
                        )
                    )
                ),
                new Statement(
                    $this->phpUnitCallFactory->createCall(
                        methodName: 'refreshCrawlerAndNavigator',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    ),
                ),
            ])
        );
    }
}
