<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilParser\ActionParser;

trait CreateFromWaitForActionDataProviderTrait
{
    public function createFromWaitForActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action (wait-for), element identifier' => [
                'action' => $actionParser->parse('wait-for $".selector"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ CRAWLER }} = {{ CLIENT }}->waitFor(\'.selector\')',
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
