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
                'expectedRenderedSource' =>
                    '{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{"assertion":{"source":"$page.url exists",' .
                    '"identifier":"$page.url","comparison":"exists"}}\'' . "\n" .
                    ');',
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
                'expectedRenderedSource' =>
                    '{{ EXAMINED }} = ' .
                    '{{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{"assertion":{"source":"$\\\".selector\\\" exists",' .
                    '"identifier":"$\\\".selector\\\"","comparison":"exists"}}\'' . "\n" .
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
                'expectedRenderedSource' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{"assertion":{"source":"$\\\".selector\\\".attribute_name exists",' .
                    '"identifier":"$\\\".selector\\\".attribute_name","comparison":"exists"}}\'' .  "\n" .
                    ');' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{"assertion":{"source":"$\\\".selector\\\".attribute_name exists",' .
                    '"identifier":"$\\\".selector\\\".attribute_name","comparison":"exists"}}\'' . "\n" .
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
                        'HAS',
                        'ELEMENT',
                    ]),
                ]),
            ],
            'exists comparison, data parameter value' => [
                'assertion' => $assertionParser->parse('$data.key exists'),
                'expectedRenderedSource' =>
                    '{{ EXAMINED }} = $key ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{"assertion":{"source":"$data.key exists",' .
                    '"identifier":"$data.key","comparison":"exists"}}\'' .  "\n" .
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
                'expectedRenderedSource' =>
                    '{{ EXAMINED }} = ' .
                    '{{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":"a[href=foo.html]"}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{"assertion":{"source":"$\\\"a[href=foo.html]\\\" exists",' .
                    '"identifier":"$\\\"a[href=foo.html]\\\"","comparison":"exists"}}\'' . "\n" .
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
                'expectedRenderedSource' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":"a[href=foo.html]"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{"assertion":{"source":"$\\\"a[href=foo.html]\\\".attribute_name exists",' .
                    '"identifier":"$\\\"a[href=foo.html]\\\".attribute_name","comparison":"exists"}}\'' . "\n" .
                    ');' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":"a[href=foo.html]"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{"assertion":{"source":"$\\\"a[href=foo.html]\\\".attribute_name exists",' .
                    '"identifier":"$\\\"a[href=foo.html]\\\".attribute_name","comparison":"exists"}}\'' . "\n" .
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
                        'HAS',
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
