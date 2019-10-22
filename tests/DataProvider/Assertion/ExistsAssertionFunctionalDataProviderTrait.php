<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

trait ExistsAssertionFunctionalDataProviderTrait
{
    public function existsAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute exists'
                ),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 exists'
                ),
                'additionalSetupStatements' => [],
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size exists'
                ),
                'additionalSetupStatements' => [],
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, page object value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title exists'
                ),
                'additionalSetupStatements' => [],
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
