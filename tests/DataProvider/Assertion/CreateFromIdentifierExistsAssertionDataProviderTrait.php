<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Statement\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromIdentifierExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierExistsAssertionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, element identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector" exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->has('{
                        "locator": ".selector"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
            'exists comparison, attribute identifier examined value' => [
                'statement' => $assertionParser->parse('$".selector".attribute_name exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->hasOne('{
                        "locator": ".selector"
                    }');
                    $attributeExists = $elementExists && ((function (): null|string {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $attributeExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $attributeExists),
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
            'exists comparison, css attribute selector containing dot' => [
                'statement' => $assertionParser->parse('$"a[href=foo.html]" exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->has('{
                        "locator": "a[href=foo.html]"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
            'exists comparison, css attribute selector containing single quotes' => [
                'statement' => $assertionParser->parse('$"[data-value=\"' . "'single quoted'" . '\"]" exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->has('{
                        "locator": "[data-value=\\"\'single quoted\'\\"]"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'statement' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->hasOne('{
                        "locator": "a[href=foo.html]"
                    }');
                    $attributeExists = $elementExists && ((function (): null|string {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": "a[href=foo.html]"
                        }');

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null;
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $attributeExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $attributeExists),
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
            'derived exists comparison, click action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('click $".selector"', 0),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->hasOne('{
                        "locator": ".selector"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
            'derived exists comparison, submit action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('submit $".selector"', 0),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->hasOne('{
                        "locator": ".selector"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
            'derived exists comparison, set action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('set $".selector" to "value"', 0),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->has('{
                        "locator": ".selector"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
            'derived exists comparison, wait action source' => [
                'statement' => new DerivedValueOperationAssertion(
                    $actionParser->parse('wait $".duration"', 0),
                    '$".duration"',
                    'exists'
                ),
                'expectedRenderedSetup' => <<< 'EOD'
                    $elementExists = {{ NAVIGATOR }}->has('{
                        "locator": ".duration"
                    }');
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $elementExists,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage($statement_0, true, $elementExists),
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
