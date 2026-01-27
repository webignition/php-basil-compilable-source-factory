<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromForwardActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromForwardActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (forward)' => [
                'statement' => $actionParser->parse('forward', 0),
                'expectedRenderedSetup' => null,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ CRAWLER }} = {{ CLIENT }}->forward();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => null,
                'expectedBodyMetadata' => new Metadata(variableNames: [
                    DependencyName::PANTHER_CRAWLER->value,
                    DependencyName::PANTHER_CLIENT->value,
                    DependencyName::PHPUNIT_TEST_CASE->value,
                ]),
            ],
        ];
    }
}
