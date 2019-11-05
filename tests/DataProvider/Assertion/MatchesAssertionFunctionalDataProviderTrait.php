<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\AssertionFactory;

trait MatchesAssertionFunctionalDataProviderTrait
{
    public function matchesAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'matches comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" matches "/^\.selector [a-z]+$/"'
                ),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'matches comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute matches "/^[a-z]+ content$/"'
                ),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'matches comparison, environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 matches "/^environment/"'
                ),
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'matches comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size matches "/[0-9]+x[0-9]+/"'
                ),
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                ],
            ],
            'matches comparison, page object examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title matches "/fixture$/"'
                ),
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'matches comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".matches-examined matches $elements.matches-expected',
                    DomIdentifierValue::create('.matches-examined'),
                    AssertionComparison::MATCHES,
                    DomIdentifierValue::create('.matches-expected')
                ),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'matches comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".selector" matches $elements.element_name.data-matches-content',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::MATCHES,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-matches-content')
                    )
                ),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'matches comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-environment-value matches $env.MATCHES'
                ),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
