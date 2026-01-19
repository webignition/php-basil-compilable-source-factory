<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierNotExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $expectedMetadata = new Metadata(
            classNames: [
                InvalidLocatorException::class,
            ],
            variableNames: [
                VariableName::PHPUNIT_TEST_CASE,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        return [
            'not-exists comparison, element identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector" not-exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $elementExists = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        $locator = $exception->getElementIdentifier()->getLocator();
                        $type = $exception->getElementIdentifier()->isCssSelector() ? 'css' : 'xpath';
                        {{ PHPUNIT }}->fail('{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" not-exists",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "operator": "not-exists"
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
                    {{ PHPUNIT }}->assertFalse(
                        $elementExists,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" not-exists",
                                "index": 0,
                                "identifier": "$\".selector\"",
                                "operator": "not-exists"
                            },
                            "expected": ' . (false ? 'true' : 'false') . ',
                            "examined": ' . ($elementExists ? 'true' : 'false') . '
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
                    classNames: [],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name not-exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    try {
                        $elementExists = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                        $attributeExists = $elementExists && ((function () {
                            $element = {{ NAVIGATOR }}->findOne('{
                                "locator": ".selector"
                            }');

                            return $element->getAttribute('attribute_name');
                        })() ?? null) !== null;
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
                                    "source": "$\".selector\".attribute_name not-exists",
                                    "index": 0,
                                    "identifier": "$\".selector\".attribute_name",
                                    "operator": "not-exists"
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
                        $elementExists,
                        '{
                            "statement": {
                                "container": {
                                    "value": "$\".selector\"",
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion"
                                },
                                "statement": {
                                    "statement-type": "assertion",
                                    "source": "$\".selector\".attribute_name not-exists",
                                    "index": 0,
                                    "identifier": "$\".selector\".attribute_name",
                                    "operator": "not-exists"
                                }
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($elementExists ? 'true' : 'false') . '
                        }'
                    );
                    {{ PHPUNIT }}->assertFalse(
                        $attributeExists,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\".attribute_name not-exists",
                                "index": 0,
                                "identifier": "$\".selector\".attribute_name",
                                "operator": "not-exists"
                            },
                            "expected": ' . (false ? 'true' : 'false') . ',
                            "examined": ' . ($attributeExists ? 'true' : 'false') . '
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
