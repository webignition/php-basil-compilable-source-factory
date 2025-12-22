<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierExistsAssertionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $expectedMetadata = new Metadata(
            classNames: [
                InvalidLocatorException::class,
            ],
            variableNames: [
                VariableName::PHPUNIT_TEST_CASE,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        return [
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\".selector\\" exists",
                                "type": "assertion"
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".selector\\" exists",
                            "type": "assertion"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\".selector\\" exists",
                                "type": "assertion",
                                "source": {
                                    "statement": "$\\".selector\\".attribute_name exists",
                                    "type": "assertion"
                                }
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".selector\\" exists",
                            "type": "assertion",
                            "source": {
                                "statement": "$\\".selector\\".attribute_name exists",
                                "type": "assertion"
                            }
                        }'
                    );
                    $examinedValue = ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".selector\\".attribute_name exists",
                            "type": "assertion"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": "a[href=foo.html]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\"a[href=foo.html]\\" exists",
                                "type": "assertion"
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\"a[href=foo.html]\\" exists",
                            "type": "assertion"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": "a[href=foo.html]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\"a[href=foo.html]\\" exists",
                                "type": "assertion",
                                "source": {
                                    "statement": "$\\"a[href=foo.html]\\".attribute_name exists",
                                    "type": "assertion"
                                }
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\"a[href=foo.html]\\" exists",
                            "type": "assertion",
                            "source": {
                                "statement": "$\\"a[href=foo.html]\\".attribute_name exists",
                                "type": "assertion"
                            }
                        }'
                    );
                    $examinedValue = ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": "a[href=foo.html]"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\"a[href=foo.html]\\".attribute_name exists",
                            "type": "assertion"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, click action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\".selector\\" exists",
                                "type": "assertion",
                                "source": {
                                    "statement": "click $\\".selector\\"",
                                    "type": "action"
                                }
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".selector\\" exists",
                            "type": "assertion",
                            "source": {
                                "statement": "click $\\".selector\\"",
                                "type": "action"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, submit action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('submit $".selector"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\".selector\\" exists",
                                "type": "assertion",
                                "source": {
                                    "statement": "submit $\\".selector\\"",
                                    "type": "action"
                                }
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".selector\\" exists",
                            "type": "assertion",
                            "source": {
                                "statement": "submit $\\".selector\\"",
                                "type": "action"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, set action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('set $".selector" to "value"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\".selector\\" exists",
                                "type": "assertion",
                                "source": {
                                    "statement": "set $\\".selector\\" to \\"value\\"",
                                    "type": "action"
                                }
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".selector\\" exists",
                            "type": "assertion",
                            "source": {
                                "statement": "set $\\".selector\\" to \\"value\\"",
                                "type": "action"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, wait action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('wait $".duration"'),
                    '$".duration"',
                    'exists'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".duration"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement": "$\\".duration\\" exists",
                                "type": "assertion",
                                "source": {
                                    "statement": "wait $\\".duration\\"",
                                    "type": "action"
                                }
                            },
                            "context": {
                                "reason": "locator-invalid"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": "$\\".duration\\" exists",
                            "type": "assertion",
                            "source": {
                                "statement": "wait $\\".duration\\"",
                                "type": "action"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
