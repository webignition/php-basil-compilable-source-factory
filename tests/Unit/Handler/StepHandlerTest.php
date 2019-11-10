<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

class StepHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        StepInterface $step,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = StepHandler::createHandler();

        $source = $handler->handle($step);

        $this->assertInstanceOf(SourceInterface::class, $source);

        if ($source instanceof SourceInterface) {
            $this->assertSourceContentEquals($expectedContent, $source);
            $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
        }
    }

    public function handleDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'empty step' => [
                'step' => new Step([], []),
                'expectedContent' => new Block(),
                'expectedMetadata' => new Metadata(),
            ],
            'one action' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    []
                ),
                'expectedContent' => new Block([
                    new Comment('click ".selector"'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
                    ),
                    new Statement('{{ ELEMENT }}->click()'),
                    new EmptyLine(),
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
            'two action' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                        $actionFactory->createFromActionString('wait 1'),
                    ],
                    []
                ),
                'expectedContent' => new Block([
                    new Comment('click ".selector"'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
                    ),
                    new Statement('{{ ELEMENT }}->click()'),
                    new EmptyLine(),
                    new Comment('wait 1'),
                    new Statement('{{ DURATION }} = "1" ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                    new EmptyLine(),
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
                        'DURATION',
                        'HAS',
                        'ELEMENT',
                    ])),
            ],
            'one assertion' => [
                'step' => new Step(
                    [],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                    ]
                ),
                'expectedContent' => new Block([
                    new Comment('$page.title is "value"'),
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                    new EmptyLine(),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                    ])),
            ],
            'two assertions' => [
                'step' => new Step(
                    [],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                        $assertionFactory->createFromAssertionString('$page.url is "http://example.com"'),
                    ]
                ),
                'expectedContent' => new Block([
                    new Comment('$page.title is "value"'),
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                    new EmptyLine(),
                    new Comment('$page.url is "http://example.com"'),
                    new Statement('{{ EXPECTED }} = "http://example.com" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                    new EmptyLine(),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                    ])),
            ],
            'one action, one assertion' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                    ]
                ),
                'expectedContent' => new Block([
                    new Comment('click ".selector"'),
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
                    ),
                    new Statement('{{ ELEMENT }}->click()'),
                    new EmptyLine(),
                    new Comment('$page.title is "value"'),
                    new Statement('{{ EXPECTED }} = "value" ?? null'),
                    new Statement('{{ EXPECTED }} = (string) {{ EXPECTED }}'),
                    new Statement('{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED }} = (string) {{ EXAMINED }}'),
                    new Statement('{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})'),
                    new EmptyLine(),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'ELEMENT',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                    ])),
            ],
        ];
    }
}
