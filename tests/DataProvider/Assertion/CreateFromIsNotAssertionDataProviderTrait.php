<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
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
                'assertion' => $assertionParser->parse('$".selector" is-not "value"'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" is-not "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
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
                            \"assertion\": \"$\\\".selector\\\" is-not \\\"value\\\"\"
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
                'assertion' => $assertionParser->parse('$".selector".attribute_name is-not "value"'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector".attribute_name is-not "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $expectedValue = "value" ?? null;
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
                            \"assertion\": \"$\\\".selector\\\".attribute_name is-not \\\"value\\\"\"
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
