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
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
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
                            "statement": "$\\".selector\\" is \\"value\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$".parent" >> $".child" is "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
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
                            "statement": "$\\".parent\\" >> $\\".child\\" is \\"value\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
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
                            "statement": "$\\".selector\\".attribute_name is \\"value\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = "value" ?? null;
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement": "$browser.size is \\"value\\"",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('$env.KEY is "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    $examinedValue = {{ ENV }}['KEY'] ?? null;
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": "$env.KEY is \\"value\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$env.KEY|"default value" is "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    $examinedValue = {{ ENV }}['KEY'] ?? 'default value';
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": "$env.KEY|\\"default value\\" is \\"value\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = {{ ENV }}['KEY2'] ?? 'default value 2';
                    $examinedValue = {{ ENV }}['KEY1'] ?? 'default value 1';
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": "$env.KEY1|\\"default value 1\\" is $env.KEY2|\\"default value 2\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$page.title is "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    $examinedValue = {{ CLIENT }}->getTitle() ?? null;
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": "$page.title is \\"value\\"",
                            "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is $".parent" >> $".child"'),
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
                    "statement": "$browser.size is $\\".parent\\" >> $\\".child\\"",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is $".selector"'),
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
                    "statement": "$browser.size is $\\".selector\\"",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is $".selector".attribute_name'),
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
                    "statement": "$browser.size is $\\".selector\\".attribute_name",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
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
                    "statement": "$browser.size is $env.KEY",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY|"default value"'),
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
                    "statement": "$browser.size is $env.KEY|\\"default value\\"",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('$browser.size is $page.url'),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ CLIENT }}->getCurrentURL() ?? null;
            $examinedValue = (function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            {{ PHPUNIT }}->assertEquals(
                $expectedValue,
                $examinedValue,
                '{
                    "statement": "$browser.size is $page.url",
                    "type": "assertion"
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
                'assertion' => $assertionParser->parse('"examined" is "expected"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "expected" ?? null;
                    $examinedValue = "examined" ?? null;
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": "\\"examined\\" is \\"expected\\"",
                            "type": "assertion"
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
