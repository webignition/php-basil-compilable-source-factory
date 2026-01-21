<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'is comparison, element identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "value";
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".selector"
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\" is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" is \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".parent" >> $".child" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "value";
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".child",
                                "parent": {
                                    "locator": ".parent"
                                }
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".parent\\" >> $\\".child\\" is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".parent\\" >> $\\".child\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".parent\" >> $\".child\" is \"value\"",
                                "index": 0,
                                "identifier": "$\".parent\" >> $\".child\"",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "value";
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".selector"
                            }');

                            return $element->getAttribute('attribute_name');
                        })();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\".attribute_name is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\".attribute_name",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name is \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$browser.size is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = "value";
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is \\"value\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "\\"value\\"",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is \"value\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$env.KEY is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "value";
                        $examinedValue = {{ ENV }}['KEY'] ?? null;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$env.KEY is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$env.KEY",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$env.KEY is \"value\"",
                                "index": 0,
                                "identifier": "$env.KEY",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'statement' => $assertionParser->parse('$env.KEY|"default value" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "value";
                        $examinedValue = {{ ENV }}['KEY'] ?? 'default value';
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$env.KEY|\\"default value\\" is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$env.KEY|\\"default value\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$env.KEY|\"default value\" is \"value\"",
                                "index": 0,
                                "identifier": "$env.KEY|\"default value\"",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'statement' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = {{ ENV }}['KEY2'] ?? 'default value 2';
                        $examinedValue = {{ ENV }}['KEY1'] ?? 'default value 1';
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$env.KEY1|\\"default value 1\\" is $env.KEY2|\\"default value 2\\"",
                                    "index": 0,
                                    "identifier": "$env.KEY1|\\"default value 1\\"",
                                    "value": "$env.KEY2|\\"default value 2\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$env.KEY1|\"default value 1\" is $env.KEY2|\"default value 2\"",
                                "index": 0,
                                "identifier": "$env.KEY1|\"default value 1\"",
                                "value": "$env.KEY2|\"default value 2\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$page.title is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "value";
                        $examinedValue = {{ CLIENT }}->getTitle();
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$page.title is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$page.title",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$page.title is \"value\"",
                                "index": 0,
                                "identifier": "$page.title",
                                "value": "\"value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".parent" >> $".child"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (function () {
                $element = {{ NAVIGATOR }}->find('{
                    "locator": ".child",
                    "parent": {
                        "locator": ".parent"
                    }
                }');

                return {{ INSPECTOR }}->getValue($element);
            })();
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $\\".parent\\" >> $\\".child\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$\\".parent\\" >> $\\".child\\"",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is $\".parent\" >> $\".child\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$\".parent\" >> $\".child\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".selector"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (function () {
                $element = {{ NAVIGATOR }}->find('{
                    "locator": ".selector"
                }');

                return {{ INSPECTOR }}->getValue($element);
            })();
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $\\".selector\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$\\".selector\\"",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is $\".selector\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$\".selector\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".selector".attribute_name', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (function () {
                $element = {{ NAVIGATOR }}->findOne('{
                    "locator": ".selector"
                }');

                return $element->getAttribute('attribute_name');
            })();
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $\\".selector\\".attribute_name",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$\\".selector\\".attribute_name",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is $\".selector\".attribute_name",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$\".selector\".attribute_name",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $env.KEY', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = {{ ENV }}['KEY'] ?? null;
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $env.KEY",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$env.KEY",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is $env.KEY",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$env.KEY",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'statement' => $assertionParser->parse('$browser.size is $env.KEY|"default value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = {{ ENV }}['KEY'] ?? 'default value';
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $env.KEY|\\"default value\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$env.KEY|\\"default value\\"",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is $env.KEY|\"default value\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$env.KEY|\"default value\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $page.url', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = {{ CLIENT }}->getCurrentURL();
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ FAILURE_MESSAGE_FACTORY }}->create(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $page.url",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$page.url",
                        "operator": "is"
                    }',
                    StatementStage::SETUP,
                    $exception,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$browser.size is $page.url",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$page.url",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('"examined" is "expected"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = "expected";
                        $examinedValue = "examined";
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "\\"examined\\" is \\"expected\\"",
                                    "index": 0,
                                    "identifier": "\\"examined\\"",
                                    "value": "\\"expected\\"",
                                    "operator": "is"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "\"examined\" is \"expected\"",
                                "index": 0,
                                "identifier": "\"examined\"",
                                "value": "\"expected\"",
                                "operator": "is"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
