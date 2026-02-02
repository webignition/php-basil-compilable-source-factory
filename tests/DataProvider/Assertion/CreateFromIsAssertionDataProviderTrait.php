<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
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
                    $expectedValue = "value";
                    $examinedValue = (function (): string {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');

                        return (string) {{ INSPECTOR }}->getValue($element);
                    })();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".parent" >> $".child" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function (): string {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');

                        return (string) {{ INSPECTOR }}->getValue($element);
                    })();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = "value";
                    $examinedValue = (string) (function (): null|string {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$browser.size is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = "value";
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, environment examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$env.KEY is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = "value";
                    $examinedValue = (string) ({{ ENV }}['KEY'] ?? null);
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, environment examined value with default, literal string expected value' => [
                'statement' => $assertionParser->parse('$env.KEY|"default value" is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = "value";
                    $examinedValue = {{ ENV }}['KEY'] ?? 'default value';
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, environment examined value with default, environment examined value with default' => [
                'statement' => $assertionParser->parse('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = {{ ENV }}['KEY2'] ?? 'default value 2';
                    $examinedValue = {{ ENV }}['KEY1'] ?? 'default value 1';
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, page object examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$page.title is "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = "value";
                    $examinedValue = {{ CLIENT }}->getTitle();
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, descendant identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".parent" >> $".child"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = (function (): string {
                $element = {{ NAVIGATOR }}->find('{
                    "locator": ".child",
                    "parent": {
                        "locator": ".parent"
                    }
                }');

                return (string) {{ INSPECTOR }}->getValue($element);
            })();
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".selector"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = (function (): string {
                $element = {{ NAVIGATOR }}->find('{
                    "locator": ".selector"
                }');

                return (string) {{ INSPECTOR }}->getValue($element);
            })();
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $".selector".attribute_name', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = (string) (function (): null|string {
                $element = {{ NAVIGATOR }}->findOne('{
                    "locator": ".selector"
                }');

                return $element->getAttribute('attribute_name');
            })();
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $env.KEY', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = (string) ({{ ENV }}['KEY'] ?? null);
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value with default' => [
                'statement' => $assertionParser->parse('$browser.size is $env.KEY|"default value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = {{ ENV }}['KEY'] ?? 'default value';
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, browser object examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$browser.size is $page.url', 0),
                'expectedRenderedSetup' => <<< 'EOD'
            $expectedValue = {{ CLIENT }}->getCurrentURL();
            $examinedValue = (function (): string {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })();
            EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'is comparison, literal string examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('"examined" is "expected"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = "expected";
                    $examinedValue = "examined";
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
        ];
    }
}
