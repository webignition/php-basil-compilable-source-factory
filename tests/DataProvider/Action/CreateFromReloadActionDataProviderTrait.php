<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
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
                'expectedBodyMetadata' => new Metadata(dependencyNames: [
                    DependencyName::PANTHER_CRAWLER,
                    DependencyName::PANTHER_CLIENT,
                    DependencyName::PHPUNIT_TEST_CASE,
                ]),
            ],
        ];
    }
}
