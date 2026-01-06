<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

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
                'assertion' => $assertionParser->parse('$".selector" is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".selector\" is \"value\"",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "value": "\"value\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
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
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".parent\" >> $\".child\" is \"value\"",
                            "index": 0,
                            "identifier": "$\".parent\" >> $\".child\"",
                            "value": "\"value\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })();
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".selector\".attribute_name is \"value\"",
                            "index": 0,
                            "identifier": "$\".selector\".attribute_name",
                            "value": "\"value\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = "value";
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is \"value\"",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "\"value\"",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = {{ ENV }}['KEY'] ?? null;
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$env.KEY is \"value\"",
                            "index": 0,
                            "identifier": "$env.KEY",
                            "value": "\"value\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY
                    ],
                )
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = {{ ENV }}['KEY'] ?? 'default value';
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$env.KEY|\"default value\" is \"value\"",
                            "index": 0,
                            "identifier": "$env.KEY|\"default value\"",
                            "value": "\"value\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY
                    ],
                ),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'assertion' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = {{ ENV }}['KEY2'] ?? 'default value 2';
                    $examinedValue = {{ ENV }}['KEY1'] ?? 'default value 1';
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$env.KEY1|\"default value 1\" is $env.KEY2|\"default value 2\"",
                            "index": 0,
                            "identifier": "$env.KEY1|\"default value 1\"",
                            "value": "$env.KEY2|\"default value 2\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY
                    ],
                ),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$page.title is "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = {{ CLIENT }}->getTitle();
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$page.title is \"value\"",
                            "index": 0,
                            "identifier": "$page.title",
                            "value": "\"value\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".parent" >> $".child"', 0),
                'expectedRenderedContent' => <<<'EOD'
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
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is $\".parent\" >> $\".child\"",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "$\".parent\" >> $\".child\"",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector"', 0),
                'expectedRenderedContent' => <<<'EOD'
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
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is $\".selector\"",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "$\".selector\"",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name', 0),
                'expectedRenderedContent' => <<<'EOD'
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
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is $\".selector\".attribute_name",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "$\".selector\".attribute_name",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY', 0),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ ENV }}['KEY'] ?? null;
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is $env.KEY",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "$env.KEY",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"', 0),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ ENV }}['KEY'] ?? 'default value';
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is $env.KEY|\"default value\"",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "$env.KEY|\"default value\"",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY
                    ],
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $page.url', 0),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ CLIENT }}->getCurrentURL();
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement-type": "assertion",
                    "source": "$browser.size is $page.url",
                    "index": 0,
                    "identifier": "$browser.size",
                    "value": "$page.url",
                    "operator": "is"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('"examined" is "expected"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "expected";
                    $examinedValue = "examined";
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "\"examined\" is \"expected\"",
                            "index": 0,
                            "identifier": "\"examined\"",
                            "value": "\"expected\"",
                            "operator": "is"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
