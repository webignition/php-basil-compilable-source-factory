<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIncludesAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIncludesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'includes comparison, element identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector" includes "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = (string) ("value");
                    $examinedValue = (string) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringContainsString(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\" includes \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\"",
                                "value": "\\"value\\"",
                                "operator": "includes"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR->value,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR->value,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE->value,
                        VariableName::MESSAGE_FACTORY->value,
                    ],
                ),
            ],
            'includes comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name includes "value"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = (string) ("value");
                    $examinedValue = (string) ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })());
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringContainsString(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\".attribute_name includes \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\".attribute_name",
                                "value": "\\"value\\"",
                                "operator": "includes"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR->value,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE->value,
                        VariableName::MESSAGE_FACTORY->value,
                    ],
                ),
            ],
        ];
    }
}
