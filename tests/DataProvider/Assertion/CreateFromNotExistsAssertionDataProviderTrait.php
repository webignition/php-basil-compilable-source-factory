<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilAssertionGenerator\AssertionGenerator;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromNotExistsAssertionDataProviderTrait
{
    public function createFromNotExistsAssertionDataProvider(): array
    {
        $assertionGenerator = AssertionGenerator::createGenerator();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector" not-exists'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertFalse({{ EXAMINED }})',
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
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionGenerator->generate(
                    '".selector".attribute_name not-exists'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\')',
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null',
                    '{{ PHPUNIT }}->assertFalse({{ EXAMINED }})',
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
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
        ];
    }
}
