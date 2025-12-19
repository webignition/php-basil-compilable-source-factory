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
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertFalse(
                        $examinedValue,
                        '{
                            "assertion": "$\\".selector\\" not-exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\".selector\\".attribute_name not-exists"
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
                            "assertion": "$\\".selector\\".attribute_name not-exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
