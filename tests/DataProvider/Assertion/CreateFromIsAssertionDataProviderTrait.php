<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                            "locator": ".selector"
                        }'));
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" is \\\"value\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is comparison, descendant identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" is "value"'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".parent" >> $".child" is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                            "locator": ".child",
                            "parent": {
                                "locator": ".parent"
                            }
                        }'));
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".parent\\\" >> $\\\".child\\\" is \\\"value\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector".attribute_name is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": ".selector"
                        }'));
                    
                        return $element->getAttribute('attribute_name');
                    })());
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\".attribute_name is \\\"value\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is comparison, browser object examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = "value" ?? null;
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is \\\"value\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$env.KEY is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    {{ PHPUNIT }}->setExaminedValue({{ ENV }}['KEY'] ?? null);
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$env.KEY is \\\"value\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$env.KEY|"default value" is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    {{ PHPUNIT }}->setExaminedValue({{ ENV }}['KEY'] ?? 'default value');
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$env.KEY|\\\"default value\\\" is \\\"value\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$env.KEY1|"default value 1" is $env.KEY2|"default value 2"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = {{ ENV }}['KEY2'] ?? 'default value 2';
                    {{ PHPUNIT }}->setExaminedValue({{ ENV }}['KEY1'] ?? 'default value 1');
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$env.KEY1|\\\"default value 1\\\" is $env.KEY2|\\\"default value 2\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$page.title is "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
                    {{ PHPUNIT }}->setExaminedValue({{ CLIENT }}->getTitle() ?? null);
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"$page.title is \\\"value\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is $".parent" >> $".child"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = (function () {
                $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                    "locator": ".child",
                    "parent": {
                        "locator": ".parent"
                    }
                }'));
            
                return {{ INSPECTOR }}->getValue($element);
            })();
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is $\\\".parent\\\" >> $\\\".child\\\"\"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is $".selector"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = (function () {
                $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                    "locator": ".selector"
                }'));
            
                return {{ INSPECTOR }}->getValue($element);
            })();
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is $\\\".selector\\\"\"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is $".selector".attribute_name')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = (function () {
                $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                    "locator": ".selector"
                }'));
            
                return $element->getAttribute('attribute_name');
            })();
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();

                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is $\\\".selector\\\".attribute_name\"
                }'
            );
            EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::PANTHER_CLIENT,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'is comparison, browser object examined value, environment expected value' => [
                'assertion' => $assertionParser->parse('$browser.size is $env.KEY'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is $env.KEY')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ ENV }}['KEY'] ?? null;
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is $env.KEY\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is $env.KEY|"default value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ ENV }}['KEY'] ?? 'default value';
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is $env.KEY|\\\"default value\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$browser.size is $env.KEY|"default value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
            $expectedValue = {{ CLIENT }}->getCurrentURL() ?? null;
            {{ PHPUNIT }}->setExaminedValue((function () {
                $webDriverDimension = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize();
            
                return (string) ($webDriverDimension->getWidth()) . 'x' . (string) ($webDriverDimension->getHeight());
            })());
            {{ PHPUNIT }}->assertEquals(
                {{ PHPUNIT }}->getExpectedValue(),
                {{ PHPUNIT }}->getExaminedValue(),
                '{
                    \"assertion\": \"$browser.size is $env.KEY|\\\"default value\\\"\"
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
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('"examined" is "expected"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "expected" ?? null;
                    {{ PHPUNIT }}->setExaminedValue("examined" ?? null);
                    {{ PHPUNIT }}->assertEquals(
                        {{ PHPUNIT }}->getExpectedValue(),
                        {{ PHPUNIT }}->getExaminedValue(),
                        '{
                            \"assertion\": \"\\\"examined\\\" is \\\"expected\\\"\"
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
