<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait WaitForActionDataProviderTrait
{
    public function waitForActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (wait-for), element identifier' => [
                'action' => $actionFactory->createFromActionString(
                    'wait-for ".selector"'
                ),
            ],
        ];
    }
}
