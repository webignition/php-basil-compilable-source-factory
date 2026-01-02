<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIsRegExpAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIsRegExpAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'derived is-regexp, matches assertion with literal scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^value/"'),
                    '"/^value/"',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = "/^value/";
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "container": {
                                "value": "\"\/^value\/\"",
                                "operator": "is-regexp",
                                "type": "derived-value-operation-assertion"
                            },
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" matches \"\/^value\/\"",
                                "identifier": "$\".selector\"",
                                "value": "\"\/^value\/\"",
                                "operator": "matches"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with elemental value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"'),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".pattern-container"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "container": {
                                "value": "$\".pattern-container\"",
                                "operator": "is-regexp",
                                "type": "derived-value-operation-assertion"
                            },
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" matches $\".pattern-container\"",
                                "identifier": "$\".selector\"",
                                "value": "$\".pattern-container\"",
                                "operator": "matches"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with attribute value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name'),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".pattern-container"
                        }');

                        return $element->getAttribute('attribute_name');
                    })();
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "container": {
                                "value": "$\".pattern-container\".attribute_name",
                                "operator": "is-regexp",
                                "type": "derived-value-operation-assertion"
                            },
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$\".selector\" matches $\".pattern-container\".attribute_name",
                                "identifier": "$\".selector\"",
                                "value": "$\".pattern-container\".attribute_name",
                                "operator": "matches"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with data parameter scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $data.pattern'),
                    '$data.pattern',
                    'is-regexp'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = $pattern;
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        '{
                            "container": {
                                "value": "$data.pattern",
                                "operator": "is-regexp",
                                "type": "derived-value-operation-assertion"
                            },
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$page.title matches $data.pattern",
                                "identifier": "$page.title",
                                "value": "$data.pattern",
                                "operator": "matches"
                            }
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
