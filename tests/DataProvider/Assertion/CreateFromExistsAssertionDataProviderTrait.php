<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null',
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null',
                    '{{ PHPUNIT }}->assertTrue(' .
                        '{{ EXAMINED }}, ' .
                        '\'{"assertion":{"source":"$page.url exists","identifier":"$page.url",' .
                        '"comparison":"exists"}}\'' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue(' .
                        '{{ EXAMINED }}, ' .
                        '\'{"assertion":{"source":"$\\\".selector\\\" exists","identifier":"$\\\".selector\\\"",' .
                        '"comparison":"exists"}}\'' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue(' .
                    '{{ HAS }}, ' .
                        '\'{"assertion":{"source":"$\\\".selector\\\".attribute_name exists",' .
                        '"identifier":"$\\\".selector\\\".attribute_name","comparison":"exists"}}\'' .
                    ')',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\')',
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null',
                    '{{ PHPUNIT }}->assertTrue(' .
                    '{{ EXAMINED }}, ' .
                        '\'{"assertion":{"source":"$\\\".selector\\\".attribute_name exists",' .
                        '"identifier":"$\\\".selector\\\".attribute_name","comparison":"exists"}}\'' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'exists comparison, data parameter value' => [
                'assertion' => $assertionParser->parse('$data.key exists'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXAMINED }} = $key ?? null',
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null',
                    '{{ PHPUNIT }}->assertTrue(' .
                        '{{ EXAMINED }}, ' .
                        '\'{"assertion":{"source":"$data.key exists","identifier":"$data.key",' .
                        '"comparison":"exists"}}\'' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXAMINED }} = ' .
                    '{{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":"a[href=foo.html]"}\'))',
                    '{{ PHPUNIT }}->assertTrue(' .
                        '{{ EXAMINED }}, ' .
                        '\'{"assertion":{"source":"$\\\"a[href=foo.html]\\\" exists",' .
                        '"identifier":"$\\\"a[href=foo.html]\\\"","comparison":"exists"}}\'' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                        'ElementIdentifier::fromJson(\'{"locator":"a[href=foo.html]"}\')' .
                    ')',
                    '{{ PHPUNIT }}->assertTrue(' .
                        '{{ HAS }}, ' .
                        '\'{"assertion":{"source":"$\\\"a[href=foo.html]\\\".attribute_name exists",' .
                        '"identifier":"$\\\"a[href=foo.html]\\\".attribute_name","comparison":"exists"}}\'' .
                    ')',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":"a[href=foo.html]"}\')' .
                    ')',
                    '{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\')',
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null',
                    '{{ PHPUNIT }}->assertTrue(' .
                        '{{ EXAMINED }}, ' .
                        '\'{"assertion":{"source":"$\\\"a[href=foo.html]\\\".attribute_name exists",' .
                        '"identifier":"$\\\"a[href=foo.html]\\\".attribute_name","comparison":"exists"}}\'' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
        ];
    }
}
