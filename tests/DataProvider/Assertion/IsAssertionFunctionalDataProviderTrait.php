<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModels\Parser\AssertionParser;

trait IsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function isAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is ".selector content"'),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute is "attribute content"'),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$env.TEST1 is "environment value"'),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "1200x1100"'),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionParser->parse('$page.title is "Test fixture web server default document"'),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is $".selector"'),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is $".selector".data-is-selector-content'),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-environment-value is $env.TEST1'),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-browser-size is $browser.size'),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$".selector".data-page-title is $page.title'),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionParser->parse('$".select-none-selected" is "none-selected-1"'),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionParser->parse('$".select-has-selected" is "has-selected-2"'),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionParser->parse('$".select-none-selected option" is "none-selected-1"'),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionParser->parse('$".select-has-selected option" is "has-selected-2"'),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionParser->parse('$"input[name=radio-not-checked]" is ""'),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionParser->parse('$"input[name=radio-checked]" is "checked-2"'),
            ],
        ];

        $testCases = [];

        foreach ($this->equalityAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['assertion'] = $assertions[$testName]['assertion'];
            $testCases['is comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
