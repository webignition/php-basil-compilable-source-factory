<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilAssertionGenerator\AssertionGenerator;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

trait IncludesAssertionFunctionalDataProviderTrait
{
    public function includesAssertionFunctionalDataProvider(): array
    {
        $assertionGenerator = AssertionGenerator::createGenerator();

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector" includes "content"'
                ),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-test-attribute includes "attribute"'
                ),
            ],
            'environment examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$env.TEST1 includes "environment"'
                ),
            ],
            'browser object examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$browser.size includes "200x11"'
                ),
            ],
            'page object examined value, scalar expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$page.title includes "Assertions"'
                ),
            ],
            'element identifier examined value, element identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" includes $elements.element_name',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::INCLUDES,
                    DomIdentifierValue::create('.selector-content-duplicate')
                ),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '".selector" includes $elements.element_name.data-includes-selector-content',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::INCLUDES,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-includes-selector-content')
                    )
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-includes-environment-value includes $env.TEST1'
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-includes-browser-size includes $browser.size'
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".data-includes-page-title includes $page.title'
                ),
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
