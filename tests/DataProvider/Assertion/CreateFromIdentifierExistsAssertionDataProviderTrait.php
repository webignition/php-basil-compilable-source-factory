<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierExistsAssertionDataProvider(): array
    {
        $actionParser = ActionParser::create();
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
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\".selector\\" exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
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
                            "assertion": "$\\".selector\\".attribute_name exists"
                        }'
                    );
                    $examinedValue = ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\".selector\\".attribute_name exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": "a[href=foo.html]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\"a[href=foo.html]\\" exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->hasOne('{
                            "locator": "a[href=foo.html]"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\"a[href=foo.html]\\".attribute_name exists"
                        }'
                    );
                    $examinedValue = ((function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": "a[href=foo.html]"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\"a[href=foo.html]\\".attribute_name exists"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, click action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"',
                    'exists'
                ),
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
                            "assertion": "$\\".selector\\" exists",
                            "source": "click $\\".selector\\""
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, submit action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('submit $".selector"'),
                    '$".selector"',
                    'exists'
                ),
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
                            "assertion": "$\\".selector\\" exists",
                            "source": "submit $\\".selector\\""
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, set action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('set $".selector" to "value"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".selector"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\".selector\\" exists",
                            "source": "set $\\".selector\\" to \\"value\\""
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, wait action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('wait $".duration"'),
                    '$".duration"',
                    'exists'
                ),
                'expectedRenderedContent' => <<<'EOD'
                    try {
                        $examinedValue = {{ NAVIGATOR }}->has('{
                            "locator": ".duration"
                        }');
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "assertion": "$\\".duration\\" exists",
                            "source": "wait $\\".duration\\""
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
