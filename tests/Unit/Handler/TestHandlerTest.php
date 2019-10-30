<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\TestHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
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
        SourceInterface $expectedContent,
        MetadataInterface $expectedMetadata,
        string $expectedClassName,
        array $expectedFunctionNames
    ) {
        $source = $this->handler->createSource($test);

        $this->assertInstanceOf(ClassDefinitionInterface::class, $source);
        $this->assertSourceContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());

        if ($source instanceof ClassDefinitionInterface) {
            $this->assertSame($expectedClassName, $source->getName());

            $functionNames = [];

            foreach ($source->getFunctions() as $function) {
                $functionNames[] = $function->getName();
            }

            $this->assertSame($expectedFunctionNames, $functionNames);
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
                'expectedContent' => new ClassDefinition('', []),
                'expectedMetadata' => new Metadata(),
                'expectedClassName' => 'generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedFunctionNames' => [],
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
                'expectedContent' => new LineList([
                    new Comment('step one'),
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
                    new Statement('{{ EXAMINED_VALUE }} = {{ PANTHER_CLIENT }}->getTitle() ?? null'),
                    new Statement('{{ EXAMINED_VALUE }} = (string) {{ EXAMINED_VALUE }}'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}'
                        . '->assertEquals({{ EXPECTED_VALUE }}, {{ EXAMINED_VALUE }})'),
                    new EmptyLine(),
                ]),
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
                'expectedClassName' => 'generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedFunctionNames' => [
                    'testBdc4b8bd83e5660d1c62908dc7a7c43a',
                ],
            ],
        ];
    }
}
