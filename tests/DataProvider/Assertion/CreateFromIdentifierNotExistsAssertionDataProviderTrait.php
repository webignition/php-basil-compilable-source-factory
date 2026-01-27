<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIdentifierNotExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector" not-exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = (bool) ({{ NAVIGATOR }}->has('{
                        "locator": ".selector"
                    }'));
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\" not-exists",
                                "index": 0,
                                "identifier": "$\\".selector\\"",
                                "operator": "not-exists"
                            }',
                            false,
                            $elementExists,
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
            'not-exists comparison, attribute identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name not-exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = (bool) ({{ NAVIGATOR }}->hasOne('{
                        "locator": ".selector"
                    }'));
                    $attributeExists = $elementExists && (bool) (((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null);
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "container": {
                                    "value": "$\\".selector\\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\\".selector\\".attribute_name not-exists",
                                    "index": 0,
                                    "identifier": "$\\".selector\\".attribute_name",
                                    "operator": "not-exists"
                                }
                            }',
                            true,
                            $elementExists,
                        ),
                    );
                    {{ PHPUNIT }}->assertFalse(
                        $attributeExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$\\".selector\\".attribute_name not-exists",
                                "index": 0,
                                "identifier": "$\\".selector\\".attribute_name",
                                "operator": "not-exists"
                            }',
                            false,
                            $attributeExists,
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
