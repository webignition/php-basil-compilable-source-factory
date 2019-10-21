<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

trait UnhandledAssertionDataProviderTrait
{
    public function unhandledAssertionDataProvider(): array
    {
        return [
            'unhandled assertion: non-value object' => [
                'value' => new \stdClass(),
            ],
        ];
    }
}
