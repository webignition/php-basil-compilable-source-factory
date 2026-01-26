<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

trait CreateFromWaitActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromWaitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait action, literal' => [
                'statement' => $actionParser->parse('wait 30', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = "30";
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, element value' => [
                'statement' => $actionParser->parse('wait $".duration-selector"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".duration-selector"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, descendant element value' => [
                'statement' => $actionParser->parse('wait $".parent" >> $".child"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, single-character CSS selector element value' => [
                'statement' => $actionParser->parse('wait $"a"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": "a"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, attribute value' => [
                'statement' => $actionParser->parse('wait $".duration-selector".attribute_name', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".duration-selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, browser property' => [
                'statement' => $actionParser->parse('wait $browser.size', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $duration = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            $duration = $duration ?? 0;
            $duration = (int) $duration;
            $duration = $duration < 0 ? 0 : $duration;
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, page property' => [
                'statement' => $actionParser->parse('wait $page.title', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = {{ CLIENT }}->getTitle();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, environment value' => [
                'statement' => $actionParser->parse('wait $env.DURATION', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = {{ ENV }}['DURATION'];
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, environment value with default' => [
                'statement' => $actionParser->parse('wait $env.DURATION|"3"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = {{ ENV }}['DURATION'];
                    $duration = $duration ?? 3;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(),
            ],
            'wait action, data parameter' => [
                'statement' => $actionParser->parse('wait $data.key', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $duration = $key;
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    $duration = $duration < 0 ? 0 : $duration;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    usleep($duration * 1000);
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
                'expectedBodyMetadata' => new Metadata(),
            ],
        ];
    }
}
