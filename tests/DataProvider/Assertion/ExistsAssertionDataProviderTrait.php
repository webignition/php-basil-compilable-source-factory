<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModelFactory\AssertionFactory;

trait ExistsAssertionDataProviderTrait
{
    public function existsAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, page property examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.url exists'
                ),
            ],
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name exists'
                ),
            ],
        ];
    }
}
