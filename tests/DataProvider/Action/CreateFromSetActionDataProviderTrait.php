<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
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
                'statement' => $actionParser->parse('set $".selector" to "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = "value";
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, element identifier, element value' => [
                'statement' => $actionParser->parse('set $".selector" to $".source"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".source"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, element identifier, attribute value' => [
                'statement' => $actionParser->parse('set $".selector" to $".source".attribute_name', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".source"
                        }');

                        return (string) $element->getAttribute('attribute_name');
                    })();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, browser property' => [
                'statement' => $actionParser->parse('set $".selector" to $browser.size', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $setValueCollection = {{ NAVIGATOR }}->find('{
                "locator": ".selector"
            }');
            $setValueValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, page property' => [
                'statement' => $actionParser->parse('set $".selector" to $page.url', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ CLIENT }}->getCurrentURL();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value' => [
                'statement' => $actionParser->parse('set $".selector" to $env.KEY', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ ENV }}['KEY'];
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value with default' => [
                'statement' => $actionParser->parse('set $".selector" to $env.KEY|"default"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ ENV }}['KEY'] ?? 'default';
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value with default with whitespace' => [
                'statement' => $actionParser->parse('set $".selector" to $env.KEY|"default value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = {{ ENV }}['KEY'] ?? 'default value';
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, parent > child element identifier, literal value' => [
                'statement' => $actionParser->parse('set $".parent" >> $".child" to "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".child",
                        "parent": {
                            "locator": ".parent"
                        }
                    }');
                    $setValueValue = "value";
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, element identifier, data parameter value' => [
                'statement' => $actionParser->parse('set $".selector" to $data.key', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $setValueCollection = {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }');
                    $setValueValue = $key;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::WEBDRIVER_ELEMENT_MUTATOR,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
