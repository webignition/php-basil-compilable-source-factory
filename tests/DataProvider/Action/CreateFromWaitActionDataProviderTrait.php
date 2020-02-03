<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = "30" ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, element value' => [
                'action' => $actionParser->parse('wait $".duration-selector"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".duration-selector"}\')' .
                    ')',
                    '{{ DURATION }} = {{ INSPECTOR }}->getValue({{ DURATION }}) ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, descendant element value' => [
                'action' => $actionParser->parse('wait $"{{ $".parent" }} .child"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".child","parent":{"locator":".parent"}}\')' .
                    ')',
                    '{{ DURATION }} = {{ INSPECTOR }}->getValue({{ DURATION }}) ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait $"a"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":"a"}\')' .
                    ')',
                    '{{ DURATION }} = {{ INSPECTOR }}->getValue({{ DURATION }}) ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, attribute value' => [
                'action' => $actionParser->parse('wait $".duration-selector".attribute_name'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".duration-selector"}\')' .
                    ')',
                    '{{ DURATION }} = {{ DURATION }}->getAttribute(\'attribute_name\') ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, browser property' => [
                'action' => $actionParser->parse('wait $browser.size'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ DURATION }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                        'DURATION',
                    ])),
            ],
            'wait action, page property' => [
                'action' => $actionParser->parse('wait $page.title'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ CLIENT }}->getTitle() ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, environment value' => [
                'action' => $actionParser->parse('wait $env.DURATION'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ ENV }}[\'DURATION\'] ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, environment value with default' => [
                'action' => $actionParser->parse('wait $env.DURATION|"3"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ DURATION }} = {{ ENV }}[\'DURATION\'] ?? 3',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
        ];
    }
}
