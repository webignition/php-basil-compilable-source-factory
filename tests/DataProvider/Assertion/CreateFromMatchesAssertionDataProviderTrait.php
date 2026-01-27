<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
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
                    $expectedValue = (string) ("/^value/");
                    $examinedValue = (string) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })());
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
                    variableNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR->value,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR->value,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        DependencyName::PHPUNIT_TEST_CASE->value,
                        DependencyName::MESSAGE_FACTORY->value,
                    ],
                ),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name matches "/^value/"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = (string) ("/^value/");
                    $examinedValue = (string) ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })());
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
                    variableNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR->value,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        DependencyName::PHPUNIT_TEST_CASE->value,
                        DependencyName::MESSAGE_FACTORY->value,
                    ],
                ),
            ],
        ];
    }
}
