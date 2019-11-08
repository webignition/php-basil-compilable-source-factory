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
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

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
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        TestInterface $test,
        string $expectedClassName,
        array $expectedMethods,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($test);

        $this->assertInstanceOf(ClassDefinitionInterface::class, $source);

        if ($source instanceof ClassDefinitionInterface) {
            $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());

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

    public function handleDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        $expectedSetupMethodClassDependencies = new ClassDependencyCollection([
            new ClassDependency(Navigator::class),
            new ClassDependency(Inspector::class),
            new ClassDependency(Mutator::class),
        ]);

        return [
            'empty test' => [
                'step' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
                'expectedClassName' => 'generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedMethods' => [
                    $this->createExpectedSetupMethodDefinition('test name'),
                    new MethodDefinition('testOpen', new LineList([
                        new Statement('{{ PANTHER_CLIENT }}->request(\'GET\', \'http://example.com\')'),
                    ])),
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies($expectedSetupMethodClassDependencies)
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
                    $this->createExpectedSetupMethodDefinition('test name'),
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
                    ->withClassDependencies(
                        $expectedSetupMethodClassDependencies->withAdditionalItems([
                            new ClassDependency(ElementLocator::class),
                        ])
                    )
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

    private function createExpectedSetupMethodDefinition(string $testName): MethodDefinitionInterface
    {
        return new MethodDefinition('setUp', new LineList([
            new Statement('$this->setName(\'' . $testName . '\')'),
            new Statement('self::$crawler = self::$client->refreshCrawler()'),
            new Statement('$this->navigator = Navigator::create(self::$crawler)'),
            new Statement('$this->inspector = Inspector::create()'),
            new Statement('$this->mutator = Mutator::create()'),
        ]));
    }
}
