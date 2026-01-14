<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;

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
                'action' => $actionParser->parse('set $".selector" to "value"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = "value";
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $".source"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".source"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $".source".attribute_name', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".source"
                        }');

                        return $element->getAttribute('attribute_name');
                    })();
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $browser.size', 0),
                'expectedRenderedSource' => <<<'EOD'
        $setValueCollection = {{ NAVIGATOR }}->find('{
            "locator": ".selector"
        }');
        $setValueValue = (function () {
            $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

            return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
        })();
        
        {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $page.url', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ CLIENT }}->getCurrentURL();
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $env.KEY', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ ENV }}['KEY'];
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ ENV }}['KEY'] ?? 'default';
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $env.KEY|"default value"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ ENV }}['KEY'] ?? 'default value';
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".parent" >> $".child" to "value"', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".child",
                        "parent": {
                            "locator": ".parent"
                        }
                    }');
                    $setValueValue = "value";
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
                'action' => $actionParser->parse('set $".selector" to $data.key', 0),
                'expectedRenderedSource' => <<<'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = $key;
                    
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
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
