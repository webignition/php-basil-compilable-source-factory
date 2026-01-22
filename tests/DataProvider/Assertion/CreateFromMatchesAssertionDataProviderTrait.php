<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromMatchesAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromMatchesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'matches comparison, element identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector" matches "/^value/"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("/^value/");
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
                                    "source": "$\\".selector\\" matches \\"\\/^value\\/\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "value": "\\"\\/^value\\/\\"",
                                    "operator": "matches"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertMatchesRegularExpression(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\" matches \\"\\/^value\\/\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\"",
                                "value": "\\"\\/^value\\/\\"",
                                "operator": "matches"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name matches "/^value/"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $expectedValue = (string) ("/^value/");
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
                                    "source": "$\\".selector\\".attribute_name matches \\"\\/^value\\/\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\".attribute_name",
                                    "value": "\\"\\/^value\\/\\"",
                                    "operator": "matches"
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertMatchesRegularExpression(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\".attribute_name matches \\"\\/^value\\/\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\".attribute_name",
                                "value": "\\"\\/^value\\/\\"",
                                "operator": "matches"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
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
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
        ];
    }
}
