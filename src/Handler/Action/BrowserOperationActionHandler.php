<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilModels\Model\Action\ActionInterface;

class BrowserOperationActionHandler
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

    /**
     * @return array{'setup': ?BodyInterface, 'body': BodyInterface}
     */
    public function handle(ActionInterface $action): array
    {
        return [
            'setup' => null,
            'body' => new Body([
                new Statement(
                    new AssignmentExpression(
                        new VariableDependency(VariableName::PANTHER_CRAWLER),
                        new ObjectMethodInvocation(
                            new VariableDependency(VariableName::PANTHER_CLIENT),
                            $action->getType()
                        )
                    )
                ),
                new Statement(
                    $this->phpUnitCallFactory->createCall('refreshCrawlerAndNavigator'),
                ),
            ]),
        ];
    }
}
