<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

class StepHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return StepHandler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $this->assertTrue($this->handler->handles(new Step([], [])));
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        StepInterface $step,
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($step);

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
                'expectedContent' => new LineList(),
                'expectedMetadata' => new Metadata(),
            ],
            'one action' => [
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    []
                ),
                'expectedContent' => new LineList([
                    new Comment('click ".selector"'),
                    new Statement('{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ ELEMENT }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
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
                'expectedContent' => new LineList([
                    new Comment('click ".selector"'),
                    new Statement('{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ ELEMENT }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
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
                'expectedContent' => new LineList([
                    new Comment('$page.title is "value"'),
                    new Statement('{{ EXPECTED_VALUE }} = "value" ?? null'),
                    new Statement('{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}'
                        . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})'),
                    new EmptyLine(),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
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
                'expectedContent' => new LineList([
                    new Comment('$page.title is "value"'),
                    new Statement('{{ EXPECTED_VALUE }} = "value" ?? null'),
                    new Statement('{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})'),
                    new EmptyLine(),
                    new Comment('$page.url is "http://example.com"'),
                    new Statement('{{ EXPECTED_VALUE }} = "http://example.com" ?? null'),
                    new Statement('{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ CLIENT }}->getCurrentURL() ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})'),
                    new EmptyLine(),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
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
                'expectedContent' => new LineList([
                    new Comment('click ".selector"'),
                    new Statement('{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ ELEMENT }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
                    ),
                    new Statement('{{ ELEMENT }}->click()'),
                    new EmptyLine(),
                    new Comment('$page.title is "value"'),
                    new Statement('{{ EXPECTED_VALUE }} = "value" ?? null'),
                    new Statement('{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}'),
                    new Statement('{{ EXAMINED_VALUE }} = {{ CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})'),
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
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
                        'HAS',
                    ])),
            ],
        ];
    }
}
