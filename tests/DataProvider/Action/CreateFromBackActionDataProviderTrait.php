<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilParser\ActionParser;

trait CreateFromBackActionDataProviderTrait
{
    public function createFromBackActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (back)' => [
                'action' => $actionParser->parse('back'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ CRAWLER }} = {{ CLIENT }}->back()',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CRAWLER,
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
        ];
    }
}
