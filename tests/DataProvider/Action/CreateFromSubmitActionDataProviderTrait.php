<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromSubmitActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromSubmitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action (submit), element identifier' => [
                'statement' => $actionParser->parse('submit $".selector"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $element = {{ NAVIGATOR }}->findOne('{
                        "locator": ".selector"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    $element->submit();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                      ],
                ),
            ],
        ];
    }
}
