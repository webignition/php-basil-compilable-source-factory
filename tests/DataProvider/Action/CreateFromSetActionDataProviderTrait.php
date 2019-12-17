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
use webignition\DomElementLocator\ElementLocator;

trait CreateFromSetActionDataProviderTrait
{
    public function createFromSetActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, element identifier, element value' => [
                'action' => $actionParser->parse('set $".selector" to $".source"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.source\'))',
                    '{{ VALUE }} = {{ INSPECTOR }}->getValue({{ VALUE }}) ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, element identifier, attribute value' => [
                'action' => $actionParser->parse('set $".selector" to $".source".attribute_name'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.source\'))',
                    '{{ VALUE }} = {{ VALUE }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, browser property' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ VALUE }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'WEBDRIVER_DIMENSION',
                        'VALUE',
                    ])),
            ],
            'input action, page property' => [
                'action' => $actionParser->parse('set $".selector" to $page.url'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ CLIENT }}->getCurrentURL() ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, environment value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, environment value with default' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default\'',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, environment value with default with whitespace' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default value"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default value\'',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
        ];
    }
}
