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

trait CreateFromSubmitActionDataProviderTrait
{
    public function createFromSubmitActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (submit), element identifier' => [
                'action' => $actionFactory->createFromActionString(
                    'submit ".selector"'
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement('{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ ELEMENT }}->submit()'),
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
