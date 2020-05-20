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

trait CreateFromWaitActionDataProviderTrait
{
    public function createFromWaitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait action, literal' => [
                'action' => $actionParser->parse('wait 30'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ("30" ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                    ]),
                ]),
            ],
            'wait action, element value' => [
                'action' => $actionParser->parse('wait $".duration-selector"'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".duration-selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'wait action, descendant element value' => [
                'action' => $actionParser->parse('wait $".parent" >> $".child"'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".child",' . "\n" .
                    '        "parent": {' . "\n" .
                    '            "locator": ".parent"' . "\n" .
                    '        }' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'wait action, single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait $"a"'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": "a"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'wait action, attribute value' => [
                'action' => $actionParser->parse('wait $".duration-selector".attribute_name'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".duration-selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'wait action, browser property' => [
                'action' => $actionParser->parse('wait $browser.size'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ((function () {' . "\n" .
                    '    {{ WEBDRIVER_DIMENSION }} = ' .
                    '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n" .
                    "\n" .
                    '    return (string) ({{ WEBDRIVER_DIMENSION }}->getWidth()) . \'x\' . ' .
                    '(string) ({{ WEBDRIVER_DIMENSION }}->getHeight());' . "\n" .
                    '})() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                        'WEBDRIVER_DIMENSION',
                    ]),
                ]),
            ],
            'wait action, page property' => [
                'action' => $actionParser->parse('wait $page.title'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ({{ CLIENT }}->getTitle() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                    ]),
                ]),
            ],
            'wait action, environment value' => [
                'action' => $actionParser->parse('wait $env.DURATION'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ({{ ENV }}[\'DURATION\'] ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                    ]),
                ]),
            ],
            'wait action, environment value with default' => [
                'action' => $actionParser->parse('wait $env.DURATION|"3"'),
                'expectedRenderedSource' =>
                    '{{ DURATION }} = (int) ({{ ENV }}[\'DURATION\'] ?? 3);' . "\n" .
                    'usleep({{ DURATION }} * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                    ]),
                ]),
            ],
        ];
    }
}
