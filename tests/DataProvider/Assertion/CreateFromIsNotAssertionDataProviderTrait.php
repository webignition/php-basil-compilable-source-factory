<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

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
                'assertion' => $assertionParser->parse('$".selector" is-not "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    {{ PHPUNIT }}->assertNotEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".selector\" is-not \"value\"",
                            "index": 0,
                            "identifier": "$\".selector\"",
                            "value": "\"value\"",
                            "operator": "is-not"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'is-not comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is-not "value"', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })();
                    {{ PHPUNIT }}->assertNotEquals(
                        $expectedValue,
                        $examinedValue,
                        '{
                            "statement-type": "assertion",
                            "source": "$\".selector\".attribute_name is-not \"value\"",
                            "index": 0,
                            "identifier": "$\".selector\".attribute_name",
                            "value": "\"value\"",
                            "operator": "is-not"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
