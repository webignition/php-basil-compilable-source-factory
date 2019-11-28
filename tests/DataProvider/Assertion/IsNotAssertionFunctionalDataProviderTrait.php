<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilAssertionGenerator\AssertionGenerator;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

trait IsNotAssertionFunctionalDataProviderTrait
{
    public function isNotAssertionFunctionalDataProvider(): array
    {
        $assertionGenerator = AssertionGenerator::createGenerator();

        $assertions =  [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector" is-not "incorrect value"'
                ),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-test-attribute is-not "incorrect value"'
                ),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$env.TEST1 is-not "incorrect value"'
                ),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$browser.size is-not "1x1"'
                ),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$page.title is-not "incorrect value"'
                ),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" is-not $elements.element_name',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::IS_NOT,
                    DomIdentifierValue::create('.secondary-selector')
                ),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" is-not $elements.element_name.data-browser-size',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::IS_NOT,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-browser-size')
                    )
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-environment-value is-not $env.NON-EXISTENT'
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-test-attribute is-not $browser.size'
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-browser-size is-not $page.title'
                ),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionGenerator->generate(
                    '".select-none-selected" is-not "incorrect value"'
                ),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionGenerator->generate(
                    '".select-has-selected" is-not "incorrect value"'
                ),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionGenerator->generate(
                    '".select-none-selected option" is-not "incorrect value"'
                ),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionGenerator->generate(
                    '".select-has-selected option" is-not "incorrect value"'
                ),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionGenerator->generate(
                    '"input[name=radio-not-checked]" is-not "incorrect value"'
                ),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionGenerator->generate(
                    '"input[name=radio-checked]" is-not "incorrect value"'
                ),
            ],
        ];

        $testCases = [];

        foreach ($this->equalityAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['assertion'] = $assertions[$testName]['assertion'];
            $testCases['is-not comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
