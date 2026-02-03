<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Statement\Assertion\DerivedValueOperationAssertion;
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
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^value/"', 0),
                    '"/^value/"',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = "/^value/";
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with elemental value' => [
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"', 0),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = (function (): string {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".pattern-container"
                        }');

                        return (string) {{ INSPECTOR }}->getValue($element);
                    })();
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
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
            'derived is-regexp, matches assertion with attribute value' => [
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name', 0),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = (string) (function (): null|string {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".pattern-container"
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })();
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
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
            'derived is-regexp, matches assertion with data parameter scalar value' => [
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $data.pattern', 0),
                    '$data.pattern',
                    'is-regexp'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = $pattern;
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $expectedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
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
