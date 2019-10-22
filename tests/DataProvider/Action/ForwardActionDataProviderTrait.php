<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait ForwardActionDataProviderTrait
{
    public function forwardActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'no-arguments action (forward)' => [
                'action' => $actionFactory->createFromActionString(
                    'forward'
                ),
            ],
        ];
    }
}
