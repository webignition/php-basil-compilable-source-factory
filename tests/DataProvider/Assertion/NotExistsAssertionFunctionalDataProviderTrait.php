<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilModels\Parser\AssertionParser;

trait NotExistsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function notExistsAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'data' => new StatementHandlerTestData(
                    '/empty.html',
                    $assertionParser->parse('$".selector" not-exists', 0),
                ),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'data' => new StatementHandlerTestData(
                    '/assertions.html',
                    $assertionParser->parse('$".selector".data-non-existent-attribute not-exists', 0),
                ),
            ],
            'not-exists comparison, environment examined value' => [
                'data' => new StatementHandlerTestData(
                    '/empty.html',
                    $assertionParser->parse('$env.NON-EXISTENT not-exists', 0),
                ),
            ],
        ];
    }
}
