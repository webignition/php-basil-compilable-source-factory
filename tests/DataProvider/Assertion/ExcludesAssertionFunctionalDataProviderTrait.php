<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModels\Parser\AssertionParser;

trait ExcludesAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function excludesAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "not-present value"'),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute excludes "not-present value"'),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$env.TEST1 excludes "not-present value"'),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$browser.size excludes "1x2"'),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$page.title excludes "not-present value"'),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" excludes $".secondary-selector"'),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" excludes $".selector".data-browser-size'),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute excludes $env.TEST1'),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute excludes $browser.size'),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute excludes $page.title'),
            ],
        ];

        $testCases = [];

        foreach (self::inclusionAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['assertion'] = $assertions[$testName]['assertion'];
            $testCases['excludes comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
