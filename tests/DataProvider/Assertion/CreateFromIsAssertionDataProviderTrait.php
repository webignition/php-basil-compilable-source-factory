<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromIsAssertionDataProviderTrait
{
    public function createFromIsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'is comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" is "value"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".child","parent":{"locator":".parent"}}\')' .
                    ')',
                    '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","attribute":"attribute_name"}\')' .
                    ')',
                    '{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
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
                'assertion' => $assertionParser->parse('$env.KEY is "value"'),
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
                'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"'),
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
                'assertion' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'),
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
                'assertion' => $assertionParser->parse('$page.title is "value"'),
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
                'assertion' => $assertionParser->parse('$browser.size is $"{{ $".parent" }} .child"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".child","parent":{"locator":".parent"}}\')' .
                    ')',
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
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
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
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","attribute":"attribute_name"}\')' .
                    ')',
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
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'WEBDRIVER_DIMENSION',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
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
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"'),
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
                'assertion' => $assertionParser->parse('$browser.size is $page.url'),
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
            'is comparison, literal string examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('"examined" is "expected"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "expected" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = "examined" ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
        ];
    }
}
