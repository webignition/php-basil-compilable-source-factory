<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;

trait ExcludesAssertionFunctionalDataProviderTrait
{
    public function excludesAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $additionalMetadata = (new Metadata())->withClassDependencies(
            new ClassDependencyCollection([
                new ClassDependency(Inspector::class),
                new ClassDependency(Navigator::class),
            ])
        );

        return [
            'excludes comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" excludes "not-present value"'
                ),
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
            'excludes comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes "not-present value"'
                ),
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
            'excludes comparison, environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 excludes "not-present value"'
                ),
                'additionalSetupStatements' => [],
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'excludes comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size excludes "1x2"'
                ),
                'additionalSetupStatements' => [],
                'additionalVariableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'excludes comparison, page object examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title excludes "not-present value"'
                ),
                'additionalSetupStatements' => [],
                'additionalVariableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'excludes comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".selector" excludes $elements.element_name',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::EXCLUDES,
                    DomIdentifierValue::create('.secondary-selector')
                ),
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
            'excludes comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new ComparisonAssertion(
                    '".selector" excludes $elements.element_name.data-browser-size',
                    DomIdentifierValue::create('.selector'),
                    AssertionComparison::EXCLUDES,
                    new DomIdentifierValue(
                        (new DomIdentifier('.selector'))->withAttributeName('data-browser-size')
                    )
                ),
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
            'excludes comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes $env.TEST1'
                ),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
            'excludes comparison, attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes $browser.size'
                ),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
            'excludes comparison, attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute excludes $page.title'
                ),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'metadata' => $additionalMetadata,
            ],
        ];
    }
}
