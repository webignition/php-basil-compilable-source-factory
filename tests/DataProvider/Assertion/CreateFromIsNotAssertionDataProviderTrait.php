<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIsNotAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIsNotAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'is-not comparison, element identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector" is-not "value"', 0),
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
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" is-not \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"value\"",
                                "operator": "is-not"
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertNotEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" is-not \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"value\"",
                                "operator": "is-not"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'is-not comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name is-not "value"', 0),
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
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name is-not \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "value": "\"value\"",
                                "operator": "is-not"
                            },
                            "reason": "assertion-setup-failed",
                            "exception": {
                                "class": "' . addcslashes($exception::class, '"\\') . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), '"\\') . '"
                            }
                        }');
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertNotEquals(
                        (string) $expectedValue,
                        (string) $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name is-not \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "value": "\"value\"",
                                "operator": "is-not"
                            },
                            "expected": "' . addcslashes((string) $expectedValue, '"\\') . '",
                            "examined": "' . addcslashes((string) $examinedValue, '"\\') . '"
                        }',
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    classNames: [
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
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
