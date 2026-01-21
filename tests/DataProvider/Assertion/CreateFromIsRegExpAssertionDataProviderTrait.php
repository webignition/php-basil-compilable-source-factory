<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BaseBasilTestCase\Enum\StatementStage;
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
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^value/"', 0),
                    '"/^value/"',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = "/^value/";
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "container": {
                                        "value": "\\"\\/^value\\/\\"",
                                        "operator": "is-regexp",
                                        "type": "derived-value-operation-assertion"
                                    },
                                    "statement": {
                                        "statement-type": "assertion",
                                        "source": "$\\".selector\\" matches \\"\\/^value\\/\\"",
                                        "index": 0,
                                        "identifier": "$\\".selector\\"",
                                        "value": "\\"\\/^value\\/\\"",
                                        "operator": "matches"
                                    }
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
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
            'derived is-regexp, matches assertion with elemental value' => [
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"', 0),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".pattern-container"
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })();
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "container": {
                                        "value": "$\\".pattern-container\\"",
                                        "operator": "is-regexp",
                                        "type": "derived-value-operation-assertion"
                                    },
                                    "statement": {
                                        "statement-type": "assertion",
                                        "source": "$\\".selector\\" matches $\\".pattern-container\\"",
                                        "index": 0,
                                        "identifier": "$\\".selector\\"",
                                        "value": "$\\".pattern-container\\"",
                                        "operator": "matches"
                                    }
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
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
            'derived is-regexp, matches assertion with attribute value' => [
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name', 0),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = (function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".pattern-container"
                            }');

                            return $element->getAttribute('attribute_name');
                        })();
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "container": {
                                        "value": "$\\".pattern-container\\".attribute_name",
                                        "operator": "is-regexp",
                                        "type": "derived-value-operation-assertion"
                                    },
                                    "statement": {
                                        "statement-type": "assertion",
                                        "source": "$\\".selector\\" matches $\\".pattern-container\\".attribute_name",
                                        "index": 0,
                                        "identifier": "$\\".selector\\"",
                                        "value": "$\\".pattern-container\\".attribute_name",
                                        "operator": "matches"
                                    }
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
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
            'derived is-regexp, matches assertion with data parameter scalar value' => [
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $data.pattern', 0),
                    '$data.pattern',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = $pattern;
                        $expectedValue = @preg_match($examinedValue, null) === false;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
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
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
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
