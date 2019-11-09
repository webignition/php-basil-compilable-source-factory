<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromNotExistsAssertionDataProviderTrait
{
    public function createFromNotExistsAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" not-exists'
                ),
                'expectedContent' => Block::fromContent([
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
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name not-exists'
                ),
                'expectedContent' => Block::fromContent([
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
