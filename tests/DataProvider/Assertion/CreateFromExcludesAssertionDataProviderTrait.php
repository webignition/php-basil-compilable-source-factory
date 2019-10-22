<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromExcludesAssertionDataProviderTrait
{
    public function createFromExcludesAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" excludes "value"'
                ),
                'expectedStatements' => [
                    '{{ EXPECTED_VALUE }} = "value" ?? null',
                    '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED_VALUE }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED_VALUE }} = {{ EXAMINED_VALUE }} = {{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ EXAMINED_VALUE }}) ?? null',
                    '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                    '{{ PHPUNIT_TEST_CASE }}->assertStringNotContainsString((string) {{ EXPECTED_VALUE }}, (string) {{ EXAMINED_VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                    ])),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name excludes "value"'
                ),
                'expectedStatements' => [
                    '{{ EXPECTED_VALUE }} = "value" ?? null',
                    '{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}',
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ EXAMINED_VALUE }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ EXAMINED_VALUE }} = {{ EXAMINED_VALUE }} = {{ EXAMINED_VALUE }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}',
                    '{{ PHPUNIT_TEST_CASE }}->assertStringNotContainsString((string) {{ EXPECTED_VALUE }}, (string) {{ EXAMINED_VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
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