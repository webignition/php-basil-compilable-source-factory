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
                'action' => $actionParser->parse('back', 0),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->back();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata(variableNames: [
                    VariableName::PANTHER_CRAWLER,
                    VariableName::PANTHER_CLIENT,
                    VariableName::PHPUNIT_TEST_CASE,
                ]),
            ],
        ];
    }
}
