<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
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
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" is "value"' => [
                        'assertion' => $assertionParser->parse('$".selector" is "value"'),
                        'message' => '$".selector" is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" is "value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$"{{ $".parent" }} .child" is "value"' => [
                        'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" is "value"'),
                        'message' => '$"{{ $".parent" }} .child" is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".child",' . "\n" .
                    '        "parent": {' . "\n" .
                    '            "locator": ".parent"' . "\n" .
                    '        }' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$"{{ $".parent" }} .child" is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector".attribute_name is "value"' => [
                        'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                        'message' => '$".selector".attribute_name is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector".attribute_name is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is "value"' => [
                        'assertion' => $assertionParser->parse('$browser.size is "value"'),
                        'message' => '$browser.size is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY is "value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$env.KEY is "value"' => [
                        'assertion' => $assertionParser->parse('$env.KEY is "value"'),
                        'message' => '$env.KEY is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ ENV }}[\'KEY\'] ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$env.KEY is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$env.KEY|"default value" is "value"' => [
                        'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"'),
                        'message' => '$env.KEY|"default value" is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ ENV }}[\'KEY\'] ?? \'default value\';' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$env.KEY|"default value" is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'assertion' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$env.KEY1|"default value 1" is $env.KEY2|"default value 2"' => [
                        'assertion' => $assertionParser->parse(
                            '$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'
                        ),
                        'message' => '$env.KEY1|"default value 1" is $env.KEY2|"default value 2" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = {{ ENV }}[\'KEY2\'] ?? \'default value 2\';' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ ENV }}[\'KEY1\'] ?? \'default value 1\';' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$env.KEY1|"default value 1" is $env.KEY2|"default value 2" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$page.title is "value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$page.title is "value"' => [
                        'assertion' => $assertionParser->parse('$page.title is "value"'),
                        'message' => '$page.title is "value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "value" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ CLIENT }}->getTitle() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$page.title is "value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $"{{ $".parent" }} .child"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is $"{{ $".parent" }} .child"' => [
                        'assertion' => $assertionParser->parse('$browser.size is $"{{ $".parent" }} .child"'),
                        'message' => '$browser.size is $"{{ $".parent" }} .child" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".child",' . "\n" .
                    '        "parent": {' . "\n" .
                    '            "locator": ".parent"' . "\n" .
                    '        }' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is $"{{ $".parent" }} .child" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is $".selector"' => [
                        'assertion' => $assertionParser->parse('$browser.size is $".selector"'),
                        'message' => '$browser.size is $".selector" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is $".selector" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is $".selector".attribute_name' => [
                        'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name'),
                        'message' => '$browser.size is $".selector".attribute_name failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is $".selector".attribute_name failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is $env.KEY' => [
                        'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
                        'message' => '$browser.size is $env.KEY failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = {{ ENV }}[\'KEY\'] ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is $env.KEY failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is $env.KEY|"default value"' => [
                        'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"'),
                        'message' => '$browser.size is $env.KEY|"default value" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = {{ ENV }}[\'KEY\'] ?? \'default value\';' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is $env.KEY|"default value" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $page.url'),
                'assertionFailureMessageFactoryCalls' => [
                    '$browser.size is $page.url' => [
                        'assertion' => $assertionParser->parse('$browser.size is $page.url'),
                        'message' => '$browser.size is $page.url failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = {{ CLIENT }}->getCurrentURL() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$browser.size is $page.url failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('"examined" is "expected"'),
                'assertionFailureMessageFactoryCalls' => [
                    '"examined" is "expected"' => [
                        'assertion' => $assertionParser->parse('"examined" is "expected"'),
                        'message' => '"examined" is "expected" failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->expectedValue = "expected" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = "examined" ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ PHPUNIT }}->expectedValue,' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'"examined" is "expected" failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
