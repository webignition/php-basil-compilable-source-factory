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
                'action' => $actionParser->parse('wait 30', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = "30";
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;
                    
                    usleep($duration * 1000);
                    EOD,

                'expectedMetadata' => new Metadata(),
            ],
            'wait action, element value' => [
                'action' => $actionParser->parse('wait $".duration-selector"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".duration-selector"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'wait action, descendant element value' => [
                'action' => $actionParser->parse('wait $".parent" >> $".child"', 0),
                'expectedRenderedSource' => <<<'EOD'
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
                    
                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'wait action, single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait $"a"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": "a"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'wait action, attribute value' => [
                'action' => $actionParser->parse('wait $".duration-selector".attribute_name', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".duration-selector"
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'wait action, browser property' => [
                'action' => $actionParser->parse('wait $browser.size', 0),
                'expectedRenderedSource' => <<<'EOD'
            $duration = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            $duration = $duration ?? 0;
            $duration = (int) $duration;

            usleep($duration * 1000);
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'wait action, page property' => [
                'action' => $actionParser->parse('wait $page.title', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = {{ CLIENT }}->getTitle();
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'wait action, environment value' => [
                'action' => $actionParser->parse('wait $env.DURATION', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = {{ ENV }}['DURATION'];
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ],
            'wait action, environment value with default' => [
                'action' => $actionParser->parse('wait $env.DURATION|"3"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = {{ ENV }}['DURATION'];
                    $duration = $duration ?? 3;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ],
            'wait action, data parameter' => [
                'action' => $actionParser->parse('wait $data.key', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $duration = $key;
                    $duration = $duration ?? 0;
                    $duration = (int) $duration;

                    usleep($duration * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }
}
