<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
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
                    $expectedValue = (string) ("value");
                    $examinedValue = (string) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\" excludes \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\"",
                                "value": "\\"value\\"",
                                "operator": "excludes"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'excludes comparison, element identifier examined value, literal string expected w/ single quotes' => [
                'statement' => $assertionParser->parse('$".selector" excludes "\'value\'"', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $expectedValue = (string) ("'value'");
                    $examinedValue = (string) ((function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');

                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\" excludes \\"\\\'value\\\'\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\"",
                                "value": "\\"\\\'value\\\'\\"",
                                "operator": "excludes"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                        DependencyName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name excludes "value"', 0),
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
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        $expectedValue,
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\".attribute_name excludes \\"value\\"",
                                "index": 0,
                                "identifier": "$\\".selector\\".attribute_name",
                                "value": "\\"value\\"",
                                "operator": "excludes"
                            }',
                            $expectedValue,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
        ];
    }
}
