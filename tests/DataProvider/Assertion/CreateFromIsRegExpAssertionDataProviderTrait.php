<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIsRegExpAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIsRegExpAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'derived is-regexp, matches assertion with literal scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^value/"', 0),
                    '"/^value/"',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = "/^value/";
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "\"\/^value\/\"",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\" matches \"\/^value\/\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "\"\/^value\/\"",
                                    "operator": "matches"
                                }
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }

                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "\"\/^value\/\"",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\" matches \"\/^value\/\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "\"\/^value\/\"",
                                    "operator": "matches"
                                }
                            },
                            "expected": ' . ($expectedValue ? 'true' : 'false') . ',
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with elemental value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"', 0),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".pattern-container"
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })();
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".pattern-container\"",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\" matches $\".pattern-container\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "$\".pattern-container\"",
                                    "operator": "matches"
                                }
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }

                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".pattern-container\"",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\" matches $\".pattern-container\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "$\".pattern-container\"",
                                    "operator": "matches"
                                }
                            },
                            "expected": ' . ($expectedValue ? 'true' : 'false') . ',
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with attribute value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name', 0),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".pattern-container"
                            }');

                            return $element->getAttribute('attribute_name');
                        })();
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".pattern-container\".attribute_name",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\" matches $\".pattern-container\".attribute_name",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "$\".pattern-container\".attribute_name",
                                    "operator": "matches"
                                }
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }

                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".pattern-container\".attribute_name",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\" matches $\".pattern-container\".attribute_name",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "$\".pattern-container\".attribute_name",
                                    "operator": "matches"
                                }
                            },
                            "expected": ' . ($expectedValue ? 'true' : 'false') . ',
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with data parameter scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $data.pattern', 0),
                    '$data.pattern',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = $pattern;
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$data.pattern",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$page.title matches $data.pattern",
                                    "index": 0,
                                    "identifier": "$page.title",
                                    "value": "$data.pattern",
                                    "operator": "matches"
                                }
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }

                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$data.pattern",
                                    "operator": "is-regexp",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$page.title matches $data.pattern",
                                    "index": 0,
                                    "identifier": "$page.title",
                                    "value": "$data.pattern",
                                    "operator": "matches"
                                }
                            },
                            "expected": ' . ($expectedValue ? 'true' : 'false') . ',
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
