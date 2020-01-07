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

trait CreateFromClickActionDataProviderTrait
{
    public function createFromClickActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action (click), element identifier' => [
                'action' => $actionParser->parse('click $".selector"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ ELEMENT }}->click()',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'ELEMENT',
                    ])),
            ],
            'interaction action (click), parent > child identifier' => [
                'action' => $actionParser->parse('click $"{{ $".parent" }} .child"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".child","parent":{"locator":".parent"}}\')' .
                    ')',
                    '{{ ELEMENT }}->click()',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'ELEMENT',
                    ])),
            ],
        ];
    }
}
