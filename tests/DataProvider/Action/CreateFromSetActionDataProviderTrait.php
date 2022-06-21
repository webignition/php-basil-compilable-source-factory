<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromSetActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createFromSetActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    "value"' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, element identifier, element value' => [
                'action' => $actionParser->parse('set $".selector" to $".source"'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    (function () {' . "\n" .
                    '        $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '            "locator": ".source"' . "\n" .
                    '        }\'));' . "\n" .
                    "\n" .
                    '        return {{ INSPECTOR }}->getValue($element);' . "\n" .
                    '    })()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, element identifier, attribute value' => [
                'action' => $actionParser->parse('set $".selector" to $".source".attribute_name'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    (function () {' . "\n" .
                    '        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '            "locator": ".source"' . "\n" .
                    '        }\'));' . "\n" .
                    "\n" .
                    '        return $element->getAttribute(\'attribute_name\');' . "\n" .
                    '    })()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, browser property' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    (function () {' . "\n" .
                    '        $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    '' . "\n" .
                    '        return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n" .
                    '    })()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, page property' => [
                'action' => $actionParser->parse('set $".selector" to $page.url'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    {{ CLIENT }}->getCurrentURL()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, environment value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    {{ ENV }}[\'KEY\']' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, environment value with default' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default"'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    {{ ENV }}[\'KEY\'] ?? \'default\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, environment value with default with whitespace' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default value"'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    {{ ENV }}[\'KEY\'] ?? \'default value\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, parent > child element identifier, literal value' => [
                'action' => $actionParser->parse('set $".parent" >> $".child" to "value"'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".child",' . "\n" .
                    '        "parent": {' . "\n" .
                    '            "locator": ".parent"' . "\n" .
                    '        }' . "\n" .
                    '    }\')),' . "\n" .
                    '    "value"' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'input action, element identifier, data parameter value' => [
                'action' => $actionParser->parse('set $".selector" to $data.key'),
                'expectedRenderedSource' => '{{ MUTATOR }}->setValue(' . "\n" .
                    '    {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\')),' . "\n" .
                    '    $key' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
