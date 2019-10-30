<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;

trait EqualityAssertionFunctionalDataProviderTrait
{
    public function equalityAssertionFunctionalDataProvider(): array
    {
        return [
            'element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'additionalSetupStatements' => null,
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'additionalSetupStatements' => null,
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'page object examined value, scalar expected value' => [
                'fixture' => '/index.html',
                'assertion' => null,
                'additionalSetupStatements' => null,
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                        new ClassDependency(Inspector::class),
                    ])
                ),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                        new ClassDependency(Inspector::class),
                    ])
                ),
            ],
            'attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
        ];
    }
}
