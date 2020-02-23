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

trait CreateFromClickActionDataProviderTrait
{
    public function createFromClickActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $expectedMetadata = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                new ClassDependency(ElementIdentifier::class),
            ]),
            Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                VariableNames::DOM_CRAWLER_NAVIGATOR,
            ]),
            Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                'ELEMENT',
            ]),
        ]);

        return [
            'interaction action (click), element identifier' => [
                'action' => $actionParser->parse('click $".selector"'),
                'expectedRenderedSource' =>
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ ELEMENT }}->click();',
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (click), parent > child identifier' => [
                'action' => $actionParser->parse('click $"{{ $".parent" }} .child"'),
                'expectedRenderedSource' =>
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".child",' .  "\n" .
                    '    "parent": {' . "\n" .
                    '        "locator": ".parent"' . "\n" .
                    '    }' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ ELEMENT }}->click();',
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (click), single-character CSS selector element identifier' => [
                'action' => $actionParser->parse('click $"a"'),
                'expectedRenderedSource' =>
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a"' . "\n" .
                    '}\')' .
                    ');' . "\n" .
                    '{{ ELEMENT }}->click();',
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
