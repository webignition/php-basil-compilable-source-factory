<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilParser\AssertionParser;

trait IncludesAssertionFunctionalDataProviderTrait
{
    public function includesAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector" includes "content"'),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute includes "attribute"'),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$env.TEST1 includes "environment"'),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$browser.size includes "200x11"'),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$page.title includes "Assertions"'),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" includes $".selector-content-duplicate"'),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse(
                    '$".selector" includes $".selector".data-includes-selector-content'
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionParser->parse(
                    '$".selector".data-includes-environment-value includes $env.TEST1'
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionParser->parse(
                    '$".selector".data-includes-browser-size includes $browser.size'
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-includes-page-title includes $page.title'),
            ],
        ];

        $testCases = [];

        foreach ($this->inclusionAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['assertion'] = $assertions[$testName]['assertion'];
            $testCases['includes comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
