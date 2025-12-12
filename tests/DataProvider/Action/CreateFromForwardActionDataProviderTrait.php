<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
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
                'action' => $actionParser->parse('forward'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->forward();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CRAWLER,
                        VariableName::PANTHER_CLIENT,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
