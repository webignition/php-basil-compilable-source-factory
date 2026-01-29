<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
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
                        Property::asDependency(DependencyName::PANTHER_CRAWLER),
                        new MethodInvocation(
                            methodName: $statement->getType(),
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: TypeCollection::object(),
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        )
                    )
                ),
                new Statement(
                    $this->phpUnitCallFactory->createRefreshCrawlerAndNavigatorCall(),
                ),
            ])
        );
    }
}
