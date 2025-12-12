<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\VariableNames;
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
                'action' => $actionParser->parse('back'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->back();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => Metadata::create(variableNames: [
                    VariableNames::PANTHER_CRAWLER,
                    VariableNames::PANTHER_CLIENT,
                    VariableNames::PHPUNIT_TEST_CASE,
                ]),
            ],
        ];
    }
}
