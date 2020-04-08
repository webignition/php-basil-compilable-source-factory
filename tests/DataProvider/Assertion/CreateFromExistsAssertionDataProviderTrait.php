<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromExistsAssertionDataProviderTrait
{
    public function createFromExistsAssertionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, page property examined value' => [
                'assertion' => $assertionParser->parse('$page.url exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$page.url exists' => [
                        'assertion' => $assertionParser->parse('$page.url exists'),
                        'message' => '$page.url exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedValue = {{ CLIENT }}->getCurrentURL() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ PHPUNIT }}->examinedValue !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$page.url exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" exists' => [
                        'assertion' => $assertionParser->parse('$".selector" exists'),
                        'message' => '$".selector" exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->has(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" exists' => [
                        'assertion' => $assertionParser->parse('$".selector" exists'),
                        'message' => '$".selector" exists failure message',
                    ],
                    '$".selector".attribute_name exists' => [
                        'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                        'message' => '$".selector".attribute_name exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->hasOne(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ PHPUNIT }}->examinedValue !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector".attribute_name exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
            'exists comparison, data parameter value' => [
                'assertion' => $assertionParser->parse('$data.key exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$data.key exists' => [
                        'assertion' => $assertionParser->parse('$data.key exists'),
                        'message' => '$data.key exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedValue = $key ?? null;' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ PHPUNIT }}->examinedValue !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$data.key exists failure message\'' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$"a[href=foo.html]" exists' => [
                        'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                        'message' => '$"a[href=foo.html]" exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a[href=foo.html]"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->has(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$"a[href=foo.html]" exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$"a[href=foo.html]" exists' => [
                        'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                        'message' => '$"a[href=foo.html]" exists failure message',
                    ],
                    '$"a[href=foo.html]".attribute_name exists' => [
                        'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                        'message' => '$"a[href=foo.html]".attribute_name exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a[href=foo.html]"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->hasOne(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$"a[href=foo.html]" exists failure message\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": "a[href=foo.html]"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ PHPUNIT }}->examinedValue !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$"a[href=foo.html]".attribute_name exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
            'derived exists comparison, click action source' => [
                'assertion' => new DerivedElementExistsAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"'
                ),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" exists' => [
                        'assertion' => new DerivedElementExistsAssertion(
                            $actionParser->parse('click $".selector"'),
                            '$".selector"'
                        ),
                        'message' => '$".selector" exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->hasOne(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, submit action source' => [
                'assertion' => new DerivedElementExistsAssertion(
                    $actionParser->parse('submit $".selector"'),
                    '$".selector"'
                ),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" exists' => [
                        'assertion' => new DerivedElementExistsAssertion(
                            $actionParser->parse('submit $".selector"'),
                            '$".selector"'
                        ),
                        'message' => '$".selector" exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->hasOne(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, set action source' => [
                'assertion' => new DerivedElementExistsAssertion(
                    $actionParser->parse('set $".selector" to "value"'),
                    '$".selector"'
                ),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" exists' => [
                        'assertion' => new DerivedElementExistsAssertion(
                            $actionParser->parse('set $".selector" to "value"'),
                            '$".selector"'
                        ),
                        'message' => '$".selector" exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->has(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, wait action source' => [
                'assertion' => new DerivedElementExistsAssertion(
                    $actionParser->parse('wait $".duration"'),
                    '$".duration"'
                ),
                'assertionFailureMessageFactoryCalls' => [
                    '$".duration" exists' => [
                        'assertion' => new DerivedElementExistsAssertion(
                            $actionParser->parse('wait $".duration"'),
                            '$".duration"'
                        ),
                        'message' => '$".duration" exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".duration"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->has(' .
                    '{{ PHPUNIT }}->examinedElementIdentifier' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".duration" exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
