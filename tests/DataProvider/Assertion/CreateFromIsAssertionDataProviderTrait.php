<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromIsAssertionDataProviderTrait
{
    public function createFromIsAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        $browserProperty = new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size');
        $environmentValue = new ObjectValue(ObjectValueType::ENVIRONMENT_PARAMETER, '$env.KEY', 'KEY');
        $elementValue = new DomIdentifierValue(new DomIdentifier('.selector'));
        $pageProperty = new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url');

        $attributeValue = new DomIdentifierValue(
            (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
        );

        return [
            'is comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is "value"'
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ EXAMINED_VALUE }}) ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name is "value"'
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '{{ EXAMINED_VALUE }}->getAttribute(\'attribute_name\') ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size is "value"'
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ WEBDRIVER_DIMENSION }} = '
                                . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.KEY is "value"'
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = {{ ENVIRONMENT_VARIABLE_ARRAY }}[\'KEY\'] ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title is "value"'
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getTitle() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '$browser.size is ".selector"',
                    $browserProperty,
                    AssertionComparison::IS,
                    $elementValue
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = '
                                . '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ EXPECTED_VALUE }}) ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ WEBDRIVER_DIMENSION }} = '
                                . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => new ComparisonAssertion(
                    '$browser.size is ".selector".attribute_name',
                    $browserProperty,
                    AssertionComparison::IS,
                    $attributeValue
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ HAS }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = '
                                . '{{ EXPECTED_VALUE }}->getAttribute(\'attribute_name\') ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ WEBDRIVER_DIMENSION }} = '
                                . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => new ComparisonAssertion(
                    '$browser.size is $env.KEY',
                    $browserProperty,
                    AssertionComparison::IS,
                    $environmentValue
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = {{ ENVIRONMENT_VARIABLE_ARRAY }}[\'KEY\'] ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ WEBDRIVER_DIMENSION }} = '
                                . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => new ComparisonAssertion(
                    '$browser.size is $page.url',
                    $browserProperty,
                    AssertionComparison::IS,
                    $pageProperty
                ),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = {{ PANTHER_CLIENT }}->getCurrentURL() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ WEBDRIVER_DIMENSION }} = '
                                . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
        ];
    }
}
