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
use webignition\DomElementLocator\ElementLocator;

trait CreateFromMatchesAssertionDataProviderTrait
{
    public function createFromMatchesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'matches comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" matches "/^value/"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "/^value/" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertRegExp({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name matches "/^value/"'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXPECTED }} = "/^value/" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertRegExp({{ EXPECTED }}, {{ EXAMINED }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
        ];
    }
}
