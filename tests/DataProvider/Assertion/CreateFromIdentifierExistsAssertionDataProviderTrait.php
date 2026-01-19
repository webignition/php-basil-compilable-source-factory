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

        $expectedSetupMetadata = new Metadata(
            classNames: [
                InvalidLocatorException::class,
            ],
            variableNames: [
                VariableName::PHPUNIT_TEST_CASE,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        $expectedBodyMetadata = new Metadata(
            variableNames: [
                VariableName::PHPUNIT_TEST_CASE,
            ],
        );

        return [
            'exists comparison, element identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector" exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" exists",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "operator": "exists"
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" exists",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'exists comparison, attribute identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\".attribute_name exists",
                                    "index": 0,
                                    "identifier": "$\".selector\".attribute_name",
                                    "operator": "exists"
                                }
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\".attribute_name exists",
                                    "index": 0,
                                    "identifier": "$\".selector\".attribute_name",
                                    "operator": "exists"
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    try {
                        $examinedValue = ((function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".selector"
                            }');

                            return $element->getAttribute('attribute_name');
                        })() ?? null) !== null;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name exists",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "operator": "exists"
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name exists",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'exists comparison, css attribute selector containing dot' => [
                'statement' => $assertionParser->parse('$"a[href=foo.html]" exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": "a[href=foo.html]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\"a[href=foo.html]\" exists",
                                "index": 0,
                                "identifier": "$\"a[href=foo.html]\"",
                                "operator": "exists"
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\"a[href=foo.html]\" exists",
                                "index": 0,
                                "identifier": "$\"a[href=foo.html]\"",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'exists comparison, css attribute selector containing single quotes' => [
                'statement' => $assertionParser->parse('$"[data-value=\"' . "'single quoted'" . '\"]" exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": "[data-value=\\"\'single quoted\'\\"]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\"[data-value=\\\"\'single quoted\'\\\"]\" exists",
                                "index": 0,
                                "identifier": "$\"[data-value=\\\"\'single quoted\'\\\"]\"",
                                "operator": "exists"
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\"[data-value=\\\"\'single quoted\'\\\"]\" exists",
                                "index": 0,
                                "identifier": "$\"[data-value=\\\"\'single quoted\'\\\"]\"",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'statement' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": "a[href=foo.html]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\"a[href=foo.html]\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\"a[href=foo.html]\".attribute_name exists",
                                    "index": 0,
                                    "identifier": "$\"a[href=foo.html]\".attribute_name",
                                    "operator": "exists"
                                }
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\"a[href=foo.html]\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\"a[href=foo.html]\".attribute_name exists",
                                    "index": 0,
                                    "identifier": "$\"a[href=foo.html]\".attribute_name",
                                    "operator": "exists"
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    try {
                        $examinedValue = ((function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": "a[href=foo.html]"
                            }');

                            return $element->getAttribute('attribute_name');
                        })() ?? null) !== null;
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\"a[href=foo.html]\".attribute_name exists",
                                "index": 0,
                                "identifier": "$\"a[href=foo.html]\".attribute_name",
                                "operator": "exists"
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\"a[href=foo.html]\".attribute_name exists",
                                "index": 0,
                                "identifier": "$\"a[href=foo.html]\".attribute_name",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'derived exists comparison, click action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('click $".selector"', 0),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "click $\".selector\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "type": "click",
                                    "arguments": "$\".selector\""
                                }
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "click $\".selector\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "type": "click",
                                    "arguments": "$\".selector\""
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived exists comparison, submit action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('submit $".selector"', 0),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "submit $\".selector\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "type": "submit",
                                    "arguments": "$\".selector\""
                                }
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "submit $\".selector\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "type": "submit",
                                    "arguments": "$\".selector\""
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived exists comparison, set action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('set $".selector" to "value"', 0),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "set $\".selector\" to \"value\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "\"value\"",
                                    "type": "set",
                                    "arguments": "$\".selector\" to \"value\""
                                }
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "set $\".selector\" to \"value\"",
                                    "index": 0,
                                    "identifier": "$\".selector\"",
                                    "value": "\"value\"",
                                    "type": "set",
                                    "arguments": "$\".selector\" to \"value\""
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived exists comparison, wait action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('wait $".duration"', 0),
                    '$".duration"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".duration"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "container": {
                                    "value": "$\".duration\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "wait $\".duration\"",
                                    "index": 0,
                                    "value": "$\".duration\"",
                                    "type": "wait",
                                    "arguments": "$\".duration\""
                                }
                            },
                            "reason": "locator-invalid",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            },
                            "context": {
                                "locator": "' . addcslashes($locator, '"\\') . '",
                                "type": "' . addcslashes($type, '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".duration\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "action",
                                    "source": "wait $\".duration\"",
                                    "index": 0,
                                    "value": "$\".duration\"",
                                    "type": "wait",
                                    "arguments": "$\".duration\""
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
                        }'
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        InvalidLocatorException::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
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
