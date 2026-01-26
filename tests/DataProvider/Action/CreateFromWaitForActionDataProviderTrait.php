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
                VariableName::PANTHER_CRAWLER->value,
                VariableName::PANTHER_CLIENT->value,
            ],
        );

        return [
            'interaction action (wait-for), element identifier' => [
                'statement' => $actionParser->parse('wait-for $".selector"', 0),
                'expectedRenderedSetup' => null,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ CRAWLER }} = {{ CLIENT }}->waitFor('.selector');
                    EOD,
                'expectedSetupMetadata' => null,
                'expectedBodyMetadata' => $expectedMetadata,
            ],
            'interaction action (wait-for), single-character CSS selector element value' => [
                'statement' => $actionParser->parse('wait-for $"a"', 0),
                'expectedRenderedSetup' => null,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ CRAWLER }} = {{ CLIENT }}->waitFor('a');
                    EOD,
                'expectedSetupMetadata' => null,
                'expectedBodyMetadata' => $expectedMetadata,
            ],
        ];
    }
}
