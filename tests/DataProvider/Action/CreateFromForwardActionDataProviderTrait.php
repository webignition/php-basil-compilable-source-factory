<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilParser\ActionParser;

trait CreateFromForwardActionDataProviderTrait
{
    public function createFromForwardActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (forward)' => [
                'action' => $actionParser->parse('forward'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ CRAWLER }} = {{ CLIENT }}->forward()',
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
