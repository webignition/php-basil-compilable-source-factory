<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromBackActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromBackActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (back)' => [
                'statement' => $actionParser->parse('back', 0),
                'expectedRenderedSetup' => null,
                'expectedRenderedBody' => '{{ CRAWLER }} = {{ CLIENT }}->back();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedSetupMetadata' => null,
                'expectedBodyMetadata' => new Metadata(variableNames: [
                    VariableName::PANTHER_CRAWLER->value,
                    VariableName::PANTHER_CLIENT->value,
                    VariableName::PHPUNIT_TEST_CASE->value,
                ]),
            ],
        ];
    }
}
