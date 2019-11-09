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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXAMINED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\') ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement(
                        '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()'
                    ),
                    new Statement('{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ EXAMINED }} = {{ ENV }}[\'KEY\'] ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXPECTED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXPECTED }} = {{ INSPECTOR }}->getValue({{ EXPECTED }}) ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement(
                        '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()'
                    ),
                    new Statement('{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXPECTED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXPECTED }} = {{ EXPECTED }}->getAttribute(\'attribute_name\') ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement(
                        '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()'
                    ),
                    new Statement('{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = {{ ENV }}[\'KEY\'] ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement(
                        '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()'
                    ),
                    new Statement('{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = {{ CLIENT }}->getCurrentURL() ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement(
                        '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()'
                    ),
                    new Statement('{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                ]),
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
