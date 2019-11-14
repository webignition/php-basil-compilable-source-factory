<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\AssertionFactory;

trait ExcludesAssertionFunctionalDataProviderTrait
{
    public function excludesAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" excludes "not-present value"'
                ),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes "not-present value"'
                ),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 excludes "not-present value"'
                ),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size excludes "1x2"'
                ),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title excludes "not-present value"'
                ),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" excludes $elements.element_name',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::EXCLUDES,
                    DomIdentifierValue::create('.secondary-selector')
                ),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" excludes $elements.element_name.data-browser-size',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::EXCLUDES,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-browser-size')
                    )
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes $env.TEST1'
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes $browser.size'
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes $page.title'
                ),
            ],
        ];

        $testCases = [];

        foreach ($this->inclusionAssertionFunctionalDataProvider() as $testName => $testData) {
            $testData['assertion'] = $assertions[$testName]['assertion'];
            $testCases['excludes comparison, ' . $testName] = $testData;
        }

        return $testCases;
    }
}
