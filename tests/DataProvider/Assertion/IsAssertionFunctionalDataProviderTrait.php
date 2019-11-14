<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\AssertionFactory;

trait IsAssertionFunctionalDataProviderTrait
{
    public function isAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is ".selector content"'
                ),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute is "attribute content"'
                ),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 is "environment value"'
                ),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size is "1200x1100"'
                ),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title is "Test fixture web server default document"'
                ),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" is $elements.element_name',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::IS,
                    DomIdentifierValue::create('.selector')
                ),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" is $elements.element_name.data-is-selector-content',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::IS,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-is-selector-content')
                    )
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-environment-value is $env.TEST1'
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-browser-size is $browser.size'
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-page-title is $page.title'
                ),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-none-selected" is "none-selected-1"'
                ),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-has-selected" is "has-selected-2"'
                ),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-none-selected option" is "none-selected-1"'
                ),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".select-has-selected option" is "has-selected-2"'
                ),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"input[name=radio-not-checked]" is ""'
                ),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"input[name=radio-checked]" is "checked-2"'
                ),
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
