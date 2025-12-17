<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
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

        $expectedMetadata = new Metadata(
            variableNames: [
                VariableName::DOM_CRAWLER_NAVIGATOR,
                VariableName::PHPUNIT_TEST_CASE,
            ],
        );

        return [
            'interaction action (click), element identifier' => [
                'action' => $actionParser->parse('click $".selector"'),
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');
                        $element->click();
                    })();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (click), parent > child identifier' => [
                'action' => $actionParser->parse('click $".parent" >> $".child"'),
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');
                        $element->click();
                    })();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (click), single-character CSS selector element identifier' => [
                'action' => $actionParser->parse('click $"a"'),
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": "a"
                        }');
                        $element->click();
                    })();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
