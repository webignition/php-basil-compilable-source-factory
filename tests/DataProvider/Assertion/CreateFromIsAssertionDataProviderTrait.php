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

        $expectedAssertionCall = '{{ PHPUNIT }}->assertEquals(' . "\n" .
            '    {{ EXPECTED }},' . "\n" .
            '    {{ EXAMINED }},' . "\n" .
            '    \'mocked failure message\'' . "\n" .
            ');';

        return [
            'is comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                    ]),
                ]),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".child",' . "\n" .
                    '        "parent": {' . "\n" .
                    '            "locator": ".parent"' . "\n" .
                    '        }' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                    ]),
                ]),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ ENV }}[\'KEY\'] ?? null;' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ]),
                ]),
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ ENV }}[\'KEY\'] ?? \'default value\';' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ]),
                ]),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'assertion' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = {{ ENV }}[\'KEY2\'] ?? \'default value 2\';' . "\n" .
                    '{{ EXAMINED }} = {{ ENV }}[\'KEY1\'] ?? \'default value 1\';' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ]),
                ]),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$page.title is "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null;' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $"{{ $".parent" }} .child"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".child",' . "\n" .
                    '        "parent": {' . "\n" .
                    '            "locator": ".parent"' . "\n" .
                    '        }' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = {{ ENV }}[\'KEY\'] ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = {{ ENV }}[\'KEY\'] ?? \'default value\';' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $page.url'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = {{ CLIENT }}->getCurrentURL() ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('"examined" is "expected"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "expected" ?? null;' . "\n" .
                    '{{ EXAMINED }} = "examined" ?? null;' . "\n" .
                    $expectedAssertionCall
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                    ]),
                ]),
            ],
        ];
    }
}
