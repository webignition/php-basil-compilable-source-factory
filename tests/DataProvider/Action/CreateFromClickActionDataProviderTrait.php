<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromClickActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromClickActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $expectedSetupMetadata = new Metadata(
            variableNames: [
                DependencyName::DOM_CRAWLER_NAVIGATOR->value,
            ],
        );

        $expectedBodyMetadata = new Metadata(
            variableNames: [
                DependencyName::PHPUNIT_TEST_CASE->value,
            ],
        );

        return [
            'interaction action (click), element identifier' => [
                'statement' => $actionParser->parse('click $".selector"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $element = {{ NAVIGATOR }}->findOne('{
                        "locator": ".selector"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    $element->click();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => $expectedSetupMetadata,
                'expectedBodyMetadata' => $expectedBodyMetadata,
            ],
            'interaction action (click), parent > child identifier' => [
                'statement' => $actionParser->parse('click $".parent" >> $".child"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $element = {{ NAVIGATOR }}->findOne('{
                        "locator": ".child",
                        "parent": {
                            "locator": ".parent"
                        }
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    $element->click();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => $expectedSetupMetadata,
                'expectedBodyMetadata' => $expectedBodyMetadata,
            ],
            'interaction action (click), single-character CSS selector element identifier' => [
                'statement' => $actionParser->parse('click $"a"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $element = {{ NAVIGATOR }}->findOne('{
                        "locator": "a"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    $element->click();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => $expectedSetupMetadata,
                'expectedBodyMetadata' => $expectedBodyMetadata,
            ],
        ];
    }
}
