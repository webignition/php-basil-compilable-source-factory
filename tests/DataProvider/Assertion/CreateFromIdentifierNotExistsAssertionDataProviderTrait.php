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
                'assertion' => $assertionParser->parse('$".selector" not-exists', 0),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
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
                                "class": "' . addcslashes($exception::class, "'") . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), "'") . '"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertFalse(
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".selector\" not-exists",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "operator": "not-exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists', 0),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
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
                                "class": "' . addcslashes($exception::class, "'") . '",
                                "code": ' . $exception->getCode() . ',
                                "message": "' . addcslashes($exception->getMessage(), "'") . '"
                            }
                        }');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
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
                        }'
                    );
                    $examinedValue = ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    {{ PHPUNIT }}->assertFalse(
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".selector\".attribute_name not-exists",
                            "index": 0,
                            "identifier": "$\".selector\".attribute_name",
                            "operator": "not-exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
