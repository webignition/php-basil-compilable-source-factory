<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromIsNotAssertionDataProviderTrait
{
    public function createFromIsNotAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'is-not comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is-not "value"'
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXAMINED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertNotEquals({{ EXPECTED }}, {{ EXAMINED }})'),
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
            'is-not comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name is-not "value"'
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXAMINED }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXAMINED }} = {{ EXAMINED }}->getAttribute(\'attribute_name\') ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertNotEquals({{ EXPECTED }}, {{ EXAMINED }})'),
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
