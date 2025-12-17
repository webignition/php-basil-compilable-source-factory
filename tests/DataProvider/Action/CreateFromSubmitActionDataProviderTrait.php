<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

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
                'action' => $actionParser->parse('submit $".selector"'),
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');
                        $element->submit();
                    })();
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
