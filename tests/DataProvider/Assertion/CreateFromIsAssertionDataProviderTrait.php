<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilAssertionGenerator\AssertionGenerator;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\ValueFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromIsAssertionDataProviderTrait
{
    public function createFromIsAssertionDataProvider(): array
    {
        $assertionGenerator = AssertionGenerator::createGenerator();
        $valueFactory = ValueFactory::createFactory();

        $browserProperty = $valueFactory->createFromValueString('$browser.size');
        $environmentValue = $valueFactory->createFromValueString('$env.KEY');
        $environmentValueWithDefault = $valueFactory->createFromValueString('$env.KEY|"default value"');
        $elementValue = new DomIdentifierValue(new DomIdentifier('.selector'));
        $pageProperty = $valueFactory->createFromValueString('$page.url');

        $attributeValue = new DomIdentifierValue(
            (new DomIdentifier('.selector'))->withAttributeName('attribute_name')
        );

        return [
            'is comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector" is "value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'assertion' => $assertionGenerator->generate(
                    '".selector".attribute_name is "value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'assertion' => $assertionGenerator->generate(
                    '$browser.size is "value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'assertion' => $assertionGenerator->generate(
                    '$env.KEY is "value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ ENV }}[\'KEY\'] ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
            'is comparison, environment examined value with default, literal string expected value' => [
                'assertion' => $assertionGenerator->generate(
                    '$env.KEY|"default value" is "value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ ENV }}[\'KEY\'] ?? \'default value\'',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
            'is comparison, environment examined value with default, environment examined value with default' => [
                'assertion' => $assertionGenerator->generate(
                    '$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ ENV }}[\'KEY2\'] ?? \'default value 2\'',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ ENV }}[\'KEY1\'] ?? \'default value 1\'',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'assertion' => $assertionGenerator->generate(
                    '$page.title is "value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXPECTED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ EXPECTED }} = {{ INSPECTOR }}->getValue({{ EXPECTED }}) ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXPECTED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ EXPECTED }} = {{ EXPECTED }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ ENV }}[\'KEY\'] ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ EXAMINED }} = '
                    . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                    . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
            'is comparison, browser object examined value, environment expected value with default' => [
                'assertion' => new ComparisonAssertion(
                    '$browser.size is $env.KEY',
                    $browserProperty,
                    AssertionComparison::IS,
                    $environmentValueWithDefault
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ ENV }}[\'KEY\'] ?? \'default value\'',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ CLIENT }}->getCurrentURL() ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ EXAMINED }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
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
