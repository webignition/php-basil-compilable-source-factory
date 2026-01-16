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
                'statement' => $assertionParser->parse('$".selector" excludes "not-present value"', 0),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'statement' => $assertionParser->parse(
                    '$".selector".data-test-attribute excludes "not-present value"',
                    0,
                ),
            ],
            'environment examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$env.TEST1 excludes "not-present value"', 0),
            ],
            'browser object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$browser.size excludes "1x2"', 0),
            ],
            'page object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$page.title excludes "not-present value"', 0),
            ],
            'element identifier examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" excludes $".secondary-selector"', 0),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" excludes $".selector".data-browser-size', 0),
            ],
            'attribute identifier examined value, environment expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute excludes $env.TEST1', 0),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute excludes $browser.size', 0),
            ],
            'attribute identifier examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute excludes $page.title', 0),
            ],
        ];

        $testCases = [];

        foreach (self::inclusionAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['statement'] = $assertions[$testName]['statement'];
            $testCases['excludes comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
