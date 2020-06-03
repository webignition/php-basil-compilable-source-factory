<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\ResolvablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromSetActionDataProviderTrait
{
    public function createFromSetActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = "value";' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, element identifier, element value' => [
                'action' => $actionParser->parse('set $".selector" to $".source"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".source"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'input action, element identifier, attribute value' => [
                'action' => $actionParser->parse('set $".selector" to $".source".attribute_name'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".source"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'input action, browser property' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) ({{ WEBDRIVER_DIMENSION }}->getWidth()) . \'x\' . ' .
                    '(string) ({{ WEBDRIVER_DIMENSION }}->getHeight());' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'input action, page property' => [
                'action' => $actionParser->parse('set $".selector" to $page.url'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ CLIENT }}->getCurrentURL();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, environment value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'];' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, environment value with default' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default\';' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, environment value with default with whitespace' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default value"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default value\';' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, parent > child element identifier, literal value' => [
                'action' => $actionParser->parse('set $".parent" >> $".child" to "value"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".child",' . "\n" .
                    '    "parent": {' . "\n" .
                    '        "locator": ".parent"' . "\n" .
                    '    }' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = "value";' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
        ];
    }
}
