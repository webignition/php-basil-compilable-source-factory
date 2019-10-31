<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\TestHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

class TestHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return TestHandler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $this->assertTrue($this->handler->handles(new Test(
            'test name',
            new Configuration('chrome', 'http://example.com'),
            []
        )));
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        TestInterface $test,
        string $expectedClassName,
        array $expectedMethods,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createSource($test);

        $this->assertInstanceOf(ClassDefinitionInterface::class, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());

        if ($source instanceof ClassDefinitionInterface) {
            $this->assertSame($expectedClassName, $source->getName());

            $methods = $source->getMethods();
            $this->assertCount(count($expectedMethods), $methods);

            foreach ($methods as $methodIndex => $method) {
                /* @var MethodDefinitionInterface $expectedMethod */
                $expectedMethod = $expectedMethods[$methodIndex];

                $this->assertSame($expectedMethod->getName(), $method->getName());
                $this->assertSame($expectedMethod->getReturnType(), $method->getReturnType());
                $this->assertSourceContentEquals($expectedMethod, $method);
            }
        }
    }

    public function createSourceDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'empty test' => [
                'step' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
                'expectedClassName' => 'generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedMethods' => [
                    new MethodDefinition('setUp', new LineList([
                        new Statement('$this->setName(\'test name\')'),
                        new Statement('self::$crawler = self::$client->refreshCrawler()'),
                    ])),
                    new MethodDefinition('testOpen', new LineList([
                        new Statement('{{ PANTHER_CLIENT }}->request(\'GET\', \'http://example.com\')'),
                    ])),
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'single step with single action and single assertion' => [
                'step' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step one' => new Step(
                            [
                                $actionFactory->createFromActionString('click ".selector"'),
                            ],
                            [
                                $assertionFactory->createFromAssertionString('$page.title is "value"'),
                            ]
                        ),
                    ]
                ),
                'expectedClassName' => 'generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedMethods' => [
                    new MethodDefinition('setUp', new LineList([
                        new Statement('$this->setName(\'test name\')'),
                        new Statement('self::$crawler = self::$client->refreshCrawler()'),
                    ])),
                    new MethodDefinition('testOpen', new LineList([
                        new Statement('{{ PANTHER_CLIENT }}->request(\'GET\', \'http://example.com\')'),
                    ])),
                    new MethodDefinition(
                        'testBdc4b8bd83e5660d1c62908dc7a7c43a',
                        new LineList([
                            new Comment('step one'),
                            new Comment('click ".selector"'),
                            new Statement(
                                '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))'
                            ),
                            new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                            new Statement(
                                '{{ ELEMENT }} = '
                                . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))'
                            ),
                            new Statement('{{ ELEMENT }}->click()'),
                            new EmptyLine(),
                            new Comment('$page.title is "value"'),
                            new Statement('{{ EXPECTED_VALUE }} = "value" ?? null'),
                            new Statement('{{ EXPECTED_VALUE }} = (string) {{ EXPECTED_VALUE }}'),
                            new Statement('{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getTitle() ?? null'),
                            new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                            new Statement('{{ PHPUNIT_TEST_CASE }}'
                                . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})'),
                            new EmptyLine(),
                        ])
                    ),
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                        'EXAMINED_VALUE',
                        'EXPECTED_VALUE',
                    ])),
            ],
        ];
    }
}
