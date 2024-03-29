<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromForwardActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createFromForwardActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (forward)' => [
                'action' => $actionParser->parse('forward'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->forward();' . "\n" .
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
