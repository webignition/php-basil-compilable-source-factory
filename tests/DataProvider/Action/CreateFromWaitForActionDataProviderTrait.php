<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromWaitForActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromWaitForActionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $expectedMetadata = new Metadata(
            variableNames: [
                VariableName::PANTHER_CRAWLER,
                VariableName::PANTHER_CLIENT,
            ],
        );

        return [
            'interaction action (wait-for), element identifier' => [
                'action' => $actionParser->parse('wait-for $".selector"'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->waitFor(\'.selector\');',
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (wait-for), single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait-for $"a"'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->waitFor(\'a\');',
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
