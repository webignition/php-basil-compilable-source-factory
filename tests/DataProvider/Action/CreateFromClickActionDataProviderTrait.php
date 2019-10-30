<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromClickActionDataProviderTrait
{
    public function createFromClickActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (click), element identifier' => [
                'action' => $actionFactory->createFromActionString(
                    'click ".selector"'
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ ELEMENT }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ ELEMENT }}->click()'),
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
                        'ELEMENT',
                    ])),
            ],
        ];
    }
}
