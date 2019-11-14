<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\AssertionFactory;

trait IsNotAssertionFunctionalDataProviderTrait
{
    public function isNotAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $assertions =  [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is-not "incorrect value"'
                ),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute is-not "incorrect value"'
                ),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 is-not "incorrect value"'
                ),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size is-not "1x1"'
                ),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
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
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-environment-value is-not $env.NON-EXISTENT'
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute is-not $browser.size'
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-browser-size is-not $page.title'
                ),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-none-selected" is-not "incorrect value"'
                ),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-has-selected" is-not "incorrect value"'
                ),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-none-selected option" is-not "incorrect value"'
                ),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-has-selected option" is-not "incorrect value"'
                ),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"input[name=radio-not-checked]" is-not "incorrect value"'
                ),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
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
