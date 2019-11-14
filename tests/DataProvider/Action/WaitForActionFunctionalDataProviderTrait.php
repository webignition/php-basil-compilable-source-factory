<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait WaitForActionFunctionalDataProviderTrait
{
    public function waitForActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait-for action, css selector' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionFactory->createFromActionString('wait-for "#hello"'),
            ],
        ];
    }
}
