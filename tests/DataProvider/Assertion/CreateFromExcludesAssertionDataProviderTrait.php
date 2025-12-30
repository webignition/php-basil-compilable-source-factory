<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

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
                'assertion' => $assertionParser->parse('$".selector" excludes "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        (string) ($expectedValue),
                        (string) ($examinedValue),
                        '{
                            "statement": "$\\".selector\\" excludes \\"value\\"",
                            "type": "assertion"
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
            'excludes comparison, element identifier examined value, literal string expected w/ single quotes' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "\'value\'"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "'value'";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        (string) ($expectedValue),
                        (string) ($examinedValue),
                        '{
                            "statement": "$\\".selector\\" excludes \\"\'value\'\\"",
                            "type": "assertion"
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
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name excludes "value"'),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value";
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })();
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        (string) ($expectedValue),
                        (string) ($examinedValue),
                        '{
                            "statement": "$\\".selector\\".attribute_name excludes \\"value\\"",
                            "type": "assertion"
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
