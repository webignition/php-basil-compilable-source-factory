<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromSetActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromSetActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        "value"
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, element identifier, element value' => [
                'action' => $actionParser->parse('set $".selector" to $".source"'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        (function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".source"
                            }');
                    
                            return {{ INSPECTOR }}->getValue($element);
                        })()
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, element identifier, attribute value' => [
                'action' => $actionParser->parse('set $".selector" to $".source".attribute_name'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        (function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".source"
                            }');
                    
                            return $element->getAttribute('attribute_name');
                        })()
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, browser property' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size'),
                'expectedRenderedSource' => <<<'EOD'
        {{ MUTATOR }}->setValue(
            {{ NAVIGATOR }}->find('{
                "locator": ".selector"
            }'),
            (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
        
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })()
        );
        {{ PHPUNIT }}->refreshCrawlerAndNavigator();
        EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PANTHER_CLIENT,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, page property' => [
                'action' => $actionParser->parse('set $".selector" to $page.url'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        {{ CLIENT }}->getCurrentURL()
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PANTHER_CLIENT,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        {{ ENV }}['KEY']
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value with default' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default"'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        {{ ENV }}['KEY'] ?? 'default'
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value with default with whitespace' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default value"'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        {{ ENV }}['KEY'] ?? 'default value'
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, parent > child element identifier, literal value' => [
                'action' => $actionParser->parse('set $".parent" >> $".child" to "value"'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }'),
                        "value"
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, element identifier, data parameter value' => [
                'action' => $actionParser->parse('set $".selector" to $data.key'),
                'expectedRenderedSource' => <<<'EOD'
                    {{ MUTATOR }}->setValue(
                        {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }'),
                        $key
                    );
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
        ];
    }
}
