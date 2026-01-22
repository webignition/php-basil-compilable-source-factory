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
                        $expectedValue = (string) ("value");
                        $examinedValue = (string) ((function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".selector"
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })());
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\" is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\" is \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\"",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".parent" >> $".child" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("value");
                        $examinedValue = (string) ((function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".child",
                                "parent": {
                                    "locator": ".parent"
                                }
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })());
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".parent\\" >> $\\".child\\" is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".parent\\" >> $\\".child\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".parent\\" >> $\\".child\\" is \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".parent\\" >> $\\".child\\"",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("value");
                        $examinedValue = (string) ((function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".selector"
                            }');

                            return $element->getAttribute('attribute_name');
                        })());
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\".attribute_name is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\".attribute_name",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\".attribute_name is \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\".attribute_name",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$browser.size is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ("value");
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is \\"value\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "\\"value\\"",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is \\"value\\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$env.KEY is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("value");
                        $examinedValue = (string) ({{ ENV }}['KEY'] ?? null);
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$env.KEY is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$env.KEY",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$env.KEY is \\"value\\"",
                                "index": 0,
                                "identifier": "$env.KEY",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'statement' => $assertionParser->parse('$env.KEY|"default value" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("value");
                        $examinedValue = (string) ({{ ENV }}['KEY'] ?? 'default value');
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$env.KEY|\\"default value\\" is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$env.KEY|\\"default value\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$env.KEY|\\"default value\\" is \\"value\\"",
                                "index": 0,
                                "identifier": "$env.KEY|\\"default value\\"",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'statement' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ({{ ENV }}['KEY2'] ?? 'default value 2');
                        $examinedValue = (string) ({{ ENV }}['KEY1'] ?? 'default value 1');
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$env.KEY1|\\"default value 1\\" is $env.KEY2|\\"default value 2\\"",
                                    "index": 0,
                                    "identifier": "$env.KEY1|\\"default value 1\\"",
                                    "value": "$env.KEY2|\\"default value 2\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$env.KEY1|\\"default value 1\\" is $env.KEY2|\\"default value 2\\"",
                                "index": 0,
                                "identifier": "$env.KEY1|\\"default value 1\\"",
                                "value": "$env.KEY2|\\"default value 2\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$page.title is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("value");
                        $examinedValue = (string) ({{ CLIENT }}->getTitle());
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$page.title is \\"value\\"",
                                    "index": 0,
                                    "identifier": "$page.title",
                                    "value": "\\"value\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$page.title is \\"value\\"",
                                "index": 0,
                                "identifier": "$page.title",
                                "value": "\\"value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".parent" >> $".child"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ((function () {
                $element = {{ NAVIGATOR }}->find('{
                    "locator": ".child",
                    "parent": {
                        "locator": ".parent"
                    }
                }');

                return {{ INSPECTOR }}->getValue($element);
            })());
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $\\".parent\\" >> $\\".child\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$\\".parent\\" >> $\\".child\\"",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is $\\".parent\\" >> $\\".child\\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$\\".parent\\" >> $\\".child\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".selector"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ((function () {
                $element = {{ NAVIGATOR }}->find('{
                    "locator": ".selector"
                }');

                return {{ INSPECTOR }}->getValue($element);
            })());
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $\\".selector\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$\\".selector\\"",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is $\\".selector\\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$\\".selector\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".selector".attribute_name', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ((function () {
                $element = {{ NAVIGATOR }}->findOne('{
                    "locator": ".selector"
                }');

                return $element->getAttribute('attribute_name');
            })());
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $\\".selector\\".attribute_name",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$\\".selector\\".attribute_name",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is $\\".selector\\".attribute_name",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$\\".selector\\".attribute_name",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $env.KEY', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ({{ ENV }}['KEY'] ?? null);
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $env.KEY",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$env.KEY",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is $env.KEY",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$env.KEY",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'statement' => $assertionParser->parse('$browser.size is $env.KEY|"default value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ({{ ENV }}['KEY'] ?? 'default value');
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $env.KEY|\\"default value\\"",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$env.KEY|\\"default value\\"",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is $env.KEY|\\"default value\\"",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$env.KEY|\\"default value\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $page.url', 0),
                'expectedRenderedSetup' => <<< 'EOD'
        try {
            $expectedValue = (string) ({{ CLIENT }}->getCurrentURL());
            $examinedValue = (string) ((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
        } catch (\Throwable $exception) {
            {{ PHPUNIT }}->fail(
                {{ MESSAGE_FACTORY }}->createFailureMessage(
                    '{
                        "statement-type": "assertion",
                        "source": "$browser.size is $page.url",
                        "index": 0,
                        "identifier": "$browser.size",
                        "value": "$page.url",
                        "operator": "is"
                    }',
                    $exception,
                    StatementStage::SETUP,
                ),
            );
        }
        EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$browser.size is $page.url",
                                "index": 0,
                                "identifier": "$browser.size",
                                "value": "$page.url",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('"examined" is "expected"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("expected");
                        $examinedValue = (string) ("examined");
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "assertion",
                                    "source": "\\"examined\\" is \\"expected\\"",
                                    "index": 0,
                                    "identifier": "\\"examined\\"",
                                    "value": "\\"expected\\"",
                                    "operator": "is"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "\\"examined\\" is \\"expected\\"",
                                "index": 0,
                                "identifier": "\\"examined\\"",
                                "value": "\\"expected\\"",
                                "operator": "is"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
        ];
    }
}
