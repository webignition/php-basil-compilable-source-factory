<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BaseBasilTestCase\Enum\StatementStage;
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
                                    "source": "$\\".selector\\" is-not \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "value": "\\"value\\"",
                                    "operator": "is-not"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertNotEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" is-not \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"value\"",
                                "operator": "is-not"
                            },
                            "expected": "' . addcslashes($expectedValue, '"\\') . '",
                            "examined": "' . addcslashes($examinedValue, '"\\') . '"
                        }',
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
                    ],
                ),
            ],
            'is-not comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name is-not "value"', 0),
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
                                    "source": "$\\".selector\\".attribute_name is-not \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\".attribute_name",
                                    "value": "\\"value\\"",
                                    "operator": "is-not"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertNotEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name is-not \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "value": "\"value\"",
                                "operator": "is-not"
                            },
                            "expected": "' . addcslashes($expectedValue, '"\\') . '",
                            "examined": "' . addcslashes($examinedValue, '"\\') . '"
                        }',
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
                    ],
                ),
            ],
        ];
    }
}
