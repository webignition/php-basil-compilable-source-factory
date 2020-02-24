<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromExistsAssertionDataProviderTrait
{
    public function createFromExistsAssertionDataProvider(): array
    {
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
                    '{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'$page.url exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
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
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
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
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
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
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
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
                        VariableNames::EXAMINED_VALUE,
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
                    '{{ EXAMINED }} = $key ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'$data.key exists failure message\'' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
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
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a[href=foo.html]"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
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
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
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
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a[href=foo.html]"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'$"a[href=foo.html]" exists failure message\'' . "\n" .
                    ');' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": "a[href=foo.html]"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
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
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
