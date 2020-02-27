<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait CreateFromForwardActionDataProviderTrait
{
    public function createFromForwardActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'no-arguments action (forward)' => [
                'action' => $actionParser->parse('forward'),
                'expectedRenderedSource' => '{{ CRAWLER }} = {{ CLIENT }}->forward();' . "\n" .
                    '{{ CRAWLER }} = {{ CLIENT }}->refreshCrawler();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CRAWLER,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
        ];
    }
}
