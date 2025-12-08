<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModels\Parser\AssertionParser;

trait IsNotAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function isNotAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is-not "incorrect value"'),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute is-not "incorrect value"'),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$env.TEST1 is-not "incorrect value"'),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is-not "1x1"'),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$page.title is-not "incorrect value"'),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is-not $".secondary-selector"'),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is-not $".selector".data-browser-size'),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-environment-value is-not $env.NON-EXISTENT'),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute is-not $browser.size'),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-browser-size is-not $page.title'),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionParser->parse('$".select-none-selected" is-not "incorrect value"'),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionParser->parse('$".select-has-selected" is-not "incorrect value"'),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionParser->parse('$".select-none-selected option" is-not "incorrect value"'),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionParser->parse('$".select-has-selected option" is-not "incorrect value"'),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionParser->parse('$"input[name=radio-not-checked]" is-not "incorrect value"'),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionParser->parse('$"input[name=radio-checked]" is-not "incorrect value"'),
            ],
        ];

        $testCases = [];

        foreach (self::equalityAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['assertion'] = $assertions[$testName]['assertion'];
            $testCases['is-not comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
