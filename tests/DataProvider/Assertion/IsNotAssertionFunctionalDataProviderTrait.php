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
                'statement' => $assertionParser->parse('$".selector" is-not "incorrect value"', 0),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute is-not "incorrect value"', 0),
            ],
            'environment examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$env.TEST1 is-not "incorrect value"', 0),
            ],
            'browser object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$browser.size is-not "1x1"', 0),
            ],
            'page object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$page.title is-not "incorrect value"', 0),
            ],
            'element identifier examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" is-not $".secondary-selector"', 0),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" is-not $".selector".data-browser-size', 0),
            ],
            'attribute identifier examined value, environment expected value' => [
                'statement' => $assertionParser->parse(
                    '$".selector".data-environment-value is-not $env.NON-EXISTENT',
                    0,
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute is-not $browser.size', 0),
            ],
            'attribute identifier examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-browser-size is-not $page.title', 0),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'statement' => $assertionParser->parse('$".select-none-selected" is-not "incorrect value"', 0),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'statement' => $assertionParser->parse('$".select-has-selected" is-not "incorrect value"', 0),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'statement' => $assertionParser->parse('$".select-none-selected option" is-not "incorrect value"', 0),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'statement' => $assertionParser->parse('$".select-has-selected option" is-not "incorrect value"', 0),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'statement' => $assertionParser->parse('$"input[name=radio-not-checked]" is-not "incorrect value"', 0),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'statement' => $assertionParser->parse('$"input[name=radio-checked]" is-not "incorrect value"', 0),
            ],
        ];

        $testCases = [];

        foreach (self::equalityAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['statement'] = $assertions[$testName]['statement'];
            $testCases['is-not comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
