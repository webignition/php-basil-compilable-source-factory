<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromExcludesAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromExcludesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector" excludes "value"', 0),
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
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\" excludes \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "value": "\\"value\\"",
                                    "operator": "excludes"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" excludes \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"value\"",
                                "operator": "excludes"
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
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'excludes comparison, element identifier examined value, literal string expected w/ single quotes' => [
                'statement' => $assertionParser->parse('$".selector" excludes "\'value\'"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("'value'");
                        $examinedValue = (string) ((function () {
                            $element = {{ NAVIGATOR }}->find('{
                                "locator": ".selector"
                            }');

                            return {{ INSPECTOR }}->getValue($element);
                        })());
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\" excludes \\"\\\'value\\\'\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "value": "\\"\\\'value\\\'\\"",
                                    "operator": "excludes"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" excludes \"\'value\'\"",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "value": "\"\'value\'\"",
                                "operator": "excludes"
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
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name excludes "value"', 0),
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
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\".attribute_name excludes \\"value\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\".attribute_name",
                                    "value": "\\"value\\"",
                                    "operator": "excludes"
                                }',
                                StatementStage::SETUP,
                                $exception,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name excludes \"value\"",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "value": "\"value\"",
                                "operator": "excludes"
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
