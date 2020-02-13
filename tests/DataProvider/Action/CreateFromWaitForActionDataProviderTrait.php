<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait CreateFromWaitForActionDataProviderTrait
{
    public function createFromWaitForActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action (wait-for), element identifier' => [
                'action' => $actionParser->parse('wait-for $".selector"'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->waitFor(\'.selector\');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CRAWLER,
                        VariableNames::PANTHER_CLIENT,
                    ])
                ]),
            ],
            'interaction action (wait-for), single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait-for $"a"'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->waitFor(\'a\');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CRAWLER,
                        VariableNames::PANTHER_CLIENT,
                    ])
                ]),
            ],
        ];
    }
}
