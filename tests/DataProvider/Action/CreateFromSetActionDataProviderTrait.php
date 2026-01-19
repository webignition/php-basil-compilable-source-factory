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
                'statement' => $actionParser->parse('set $".selector" to "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = "value";
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"value\"",
                                "type": "set",
                                "arguments": "$\".selector\" to \"value\""
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, element identifier, element value' => [
                'statement' => $actionParser->parse('set $".selector" to $".source"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = (function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".source"
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $\".source\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$\".source\"",
                                "type": "set",
                                "arguments": "$\".selector\" to $\".source\""
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, element identifier, attribute value' => [
                'statement' => $actionParser->parse('set $".selector" to $".source".attribute_name', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = (function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".source"
                            }');

                            return $element->getAttribute('attribute_name');
                        })();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $\".source\".attribute_name",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$\".source\".attribute_name",
                                "type": "set",
                                "arguments": "$\".selector\" to $\".source\".attribute_name"
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, browser property' => [
                'statement' => $actionParser->parse('set $".selector" to $browser.size', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $setValueCollection = {{ NAVIGATOR }}->find('{
                "locator": ".selector"
            }');
            $setValueValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail('{
                "statement": {
                    "statement-type": "action",
                    "source": "set $\".selector\" to $browser.size",
                    "index": 0,
                    "identifier": "$\".selector\"",
                    "value": "$browser.size",
                    "type": "set",
                    "arguments": "$\".selector\" to $browser.size"
                },
                "reason": "action-setup-failed",
                "exception": {
                    "class": "' . addcslashes($exception::class, '"\\') . '",
                    "code": ' . $exception->getCode() . ',
                    "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                }
            }');
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PANTHER_CLIENT,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, page property' => [
                'statement' => $actionParser->parse('set $".selector" to $page.url', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = {{ CLIENT }}->getCurrentURL();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $page.url",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$page.url",
                                "type": "set",
                                "arguments": "$\".selector\" to $page.url"
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PANTHER_CLIENT,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value' => [
                'statement' => $actionParser->parse('set $".selector" to $env.KEY', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = {{ ENV }}['KEY'];
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $env.KEY",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$env.KEY",
                                "type": "set",
                                "arguments": "$\".selector\" to $env.KEY"
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value with default' => [
                'statement' => $actionParser->parse('set $".selector" to $env.KEY|"default"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = {{ ENV }}['KEY'] ?? 'default';
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $env.KEY|\"default\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$env.KEY|\"default\"",
                                "type": "set",
                                "arguments": "$\".selector\" to $env.KEY|\"default\""
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, environment value with default with whitespace' => [
                'statement' => $actionParser->parse('set $".selector" to $env.KEY|"default value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = {{ ENV }}['KEY'] ?? 'default value';
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $env.KEY|\"default value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$env.KEY|\"default value\"",
                                "type": "set",
                                "arguments": "$\".selector\" to $env.KEY|\"default value\""
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'input action, parent > child element identifier, literal value' => [
                'statement' => $actionParser->parse('set $".parent" >> $".child" to "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');
                        $setValueValue = "value";
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".parent\" >> $\".child\" to \"value\"",
                                "index": 0,
                                "identifier": "$\".parent\" >> $\".child\"",
                                "value": "\"value\"",
                                "type": "set",
                                "arguments": "$\".parent\" >> $\".child\" to \"value\""
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
            'input action, element identifier, data parameter value' => [
                'statement' => $actionParser->parse('set $".selector" to $data.key', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $setValueCollection = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                        $setValueValue = $key;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "action",
                                "source": "set $\".selector\" to $data.key",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "$data.key",
                                "type": "set",
                                "arguments": "$\".selector\" to $data.key"
                            },
                            "reason": "action-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ MUTATOR }}->setValue($setValueCollection, $setValueValue);
                    {{ PHPUNIT }}->refreshCrawlerAndNavigator();
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableName::PHPUNIT_TEST_CASE
                    ],
                ),
            ],
        ];
    }
}
