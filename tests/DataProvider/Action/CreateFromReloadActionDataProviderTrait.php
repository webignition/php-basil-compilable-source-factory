<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromReloadActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromReloadActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (reload)' => [
                'statement' => $actionParser->parse('reload', 0),
                'expectedRenderedSetup' => null,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ CRAWLER }} = {{ CLIENT }}->reload();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
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
