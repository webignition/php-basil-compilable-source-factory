<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

trait UnhandledActionsDataProviderTrait
{
    public function unhandledActionsDataProvider(): array
    {
        return [
            'unhandled action: non-value object' => [
                'action' => new \stdClass(),
            ],
        ];
    }
}
