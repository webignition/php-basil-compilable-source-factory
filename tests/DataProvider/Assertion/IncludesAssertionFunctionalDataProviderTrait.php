<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilModels\Parser\AssertionParser;

trait IncludesAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function includesAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $fixture = '/assertions.html';

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$".selector" includes "content"', 0),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute includes "attribute"', 0),
            ],
            'environment examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$env.TEST1 includes "environment"', 0),
            ],
            'browser object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$browser.size includes "200x11"', 0),
            ],
            'page object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$page.title includes "Assertions"', 0),
            ],
            'element identifier examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" includes $".selector-content-duplicate"', 0),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse(
                    '$".selector" includes $".selector".data-includes-selector-content',
                    0,
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'statement' => $assertionParser->parse(
                    '$".selector".data-includes-environment-value includes $env.TEST1',
                    0,
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'statement' => $assertionParser->parse(
                    '$".selector".data-includes-browser-size includes $browser.size',
                    0,
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-includes-page-title includes $page.title', 0),
            ],
        ];

        $testCases = [];

        foreach (self::inclusionAssertionFunctionalDataProvider() as $testName => $testData) {
            $testCases['includes comparison, ' . $testName] = [
                'data' => new StatementHandlerTestData($fixture, $assertions[$testName]['statement']),
            ];
        }

        return $testCases;
    }
}
