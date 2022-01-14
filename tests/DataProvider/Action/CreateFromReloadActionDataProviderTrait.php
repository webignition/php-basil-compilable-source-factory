<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait CreateFromReloadActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createFromReloadActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (reload)' => [
                'action' => $actionParser->parse('reload'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->reload();' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CRAWLER,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
