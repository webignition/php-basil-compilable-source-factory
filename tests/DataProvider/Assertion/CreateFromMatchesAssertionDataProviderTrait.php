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

trait CreateFromMatchesAssertionDataProviderTrait
{
    public function createFromMatchesAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'matches comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" matches "/^value/"'
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "/^value/" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ INSPECTOR }}->getValue({{ EXAMINED_VALUE }}) ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT }}->assertRegExp({{ EXPECTED }}, {{ EXAMINED_VALUE }})'),
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
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name matches "/^value/"'
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ EXPECTED }} = "/^value/" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ EXAMINED_VALUE }} = '
                        . '{{ EXAMINED_VALUE }}->getAttribute(\'attribute_name\') ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT }}->assertRegExp({{ EXPECTED }}, {{ EXAMINED_VALUE }})'),
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
