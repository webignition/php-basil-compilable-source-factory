<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
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
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = "value";' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, element identifier, element value' => [
                'action' => $actionParser->parse('set $".selector" to $".source"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".source"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'input action, element identifier, attribute value' => [
                'action' => $actionParser->parse('set $".selector" to $".source".attribute_name'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".source"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'input action, browser property' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . ' .
                    '(string) {{ WEBDRIVER_DIMENSION }}->getHeight();' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'input action, page property' => [
                'action' => $actionParser->parse('set $".selector" to $page.url'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ CLIENT }}->getCurrentURL();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, environment value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'];' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, environment value with default' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default\';' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, environment value with default with whitespace' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default value"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default value\';' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
            'input action, parent > child element identifier, literal value' => [
                'action' => $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                'expectedRenderedSource' =>
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".child","parent":{"locator":".parent"}}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = "value";' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
            ],
        ];
    }
}
