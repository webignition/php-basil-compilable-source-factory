<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromIsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'is comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".child",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent"' . "\n"
                    . '        }' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue({{ ENV }}[\'KEY\'] ?? null);' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue({{ ENV }}[\'KEY\'] ?? \'default value\');' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'assertion' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->'
                    . 'setExpectedValue({{ ENV }}[\'KEY2\'] ?? \'default value 2\');' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue({{ ENV }}[\'KEY1\'] ?? \'default value 1\');' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$page.title is "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue({{ CLIENT }}->getTitle() ?? null);' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".parent" >> $".child"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".child",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent"' . "\n"
                    . '        }' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue({{ ENV }}[\'KEY\'] ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->'
                    . 'setExpectedValue({{ ENV }}[\'KEY\'] ?? \'default value\');' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $page.url'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->'
                    . 'setExpectedValue({{ CLIENT }}->getCurrentURL() ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('"examined" is "expected"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("expected" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue("examined" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->assertEquals(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
