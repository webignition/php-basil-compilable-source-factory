<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

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
                'action' => $actionParser->parse('wait 30'),
                'expectedRenderedSource' => 'usleep(((int) ("30" ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(),
            ],
            'wait action, element value' => [
                'action' => $actionParser->parse('wait $".duration-selector"'),
                'expectedRenderedSource' => <<<'EOD'
                    usleep(((int) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".duration-selector"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })() ?? 0)) * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'wait action, descendant element value' => [
                'action' => $actionParser->parse('wait $".parent" >> $".child"'),
                'expectedRenderedSource' => <<<'EOD'
                    usleep(((int) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })() ?? 0)) * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'wait action, single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait $"a"'),
                'expectedRenderedSource' => <<<'EOD'
                    usleep(((int) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": "a"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })() ?? 0)) * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'wait action, attribute value' => [
                'action' => $actionParser->parse('wait $".duration-selector".attribute_name'),
                'expectedRenderedSource' => <<<'EOD'
                    usleep(((int) ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".duration-selector"
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })() ?? 0)) * 1000);
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'wait action, browser property' => [
                'action' => $actionParser->parse('wait $browser.size'),
                'expectedRenderedSource' => 'usleep(((int) ((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'wait action, page property' => [
                'action' => $actionParser->parse('wait $page.title'),
                'expectedRenderedSource' => 'usleep(((int) ({{ CLIENT }}->getTitle() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'wait action, environment value' => [
                'action' => $actionParser->parse('wait $env.DURATION'),
                'expectedRenderedSource' => 'usleep(((int) ({{ ENV }}[\'DURATION\'] ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ],
            'wait action, environment value with default' => [
                'action' => $actionParser->parse('wait $env.DURATION|"3"'),
                'expectedRenderedSource' => 'usleep(((int) ({{ ENV }}[\'DURATION\'] ?? 3)) * 1000);',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ],
            'wait action, data parameter' => [
                'action' => $actionParser->parse('wait $data.key'),
                'expectedRenderedSource' => 'usleep(((int) ($key ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }
}
