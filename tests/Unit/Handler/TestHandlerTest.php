<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\TestHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

class TestHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        TestInterface $test,
        string $expectedClassName,
        array $expectedMethods,
        MetadataInterface $expectedMetadata
    ) {
        $handler = TestHandler::createHandler();
        $source = $handler->handle($test);

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
                $this->assertEquals($expectedMethod->getDocBlock(), $method->getDocBlock());
                $this->assertSame($expectedMethod->getReturnType(), $method->getReturnType());
                $this->assertSame($expectedMethod->isStatic(), $method->isStatic());
                $this->assertSame($expectedMethod->getArguments(), $method->getArguments());
                $this->assertBlockContentEquals($expectedMethod, $method);
            }
        }
    }

    public function handleDataProvider(): array
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
                'expectedClassName' => 'Generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedMethods' => [
                    'setUpBeforeClass' => $this->createExpectedSetUpBeforeClassMethodDefinition('http://example.com'),
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
                'expectedClassName' => 'Generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedMethods' => [
                    'setUpBeforeClass' => $this->createExpectedSetUpBeforeClassMethodDefinition('http://example.com'),
                    'testBdc4b8bd83e5660d1c62908dc7a7c43a' => new MethodDefinition(
                        'testBdc4b8bd83e5660d1c62908dc7a7c43a',
                        CodeBlock::fromContent([
                            '//step one',
                            '//click ".selector"',
                            '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                            '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                            '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                            '{{ ELEMENT }}->click()',
                            '',
                            '//$page.title is "value"',
                            '{{ EXPECTED }} = "value" ?? null',
                            '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                            '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                            '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                            '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                            '',
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
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                    ])),
            ],
            'single step with single action and single assertion with data provider' => [
                'step' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step one' => (new Step(
                            [
                                $actionFactory->createFromActionString('set ".selector" to $data.field_value'),
                            ],
                            [
                                $assertionFactory->createFromAssertionString('".selector" is $data.expected_value'),
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet(
                                '0',
                                [
                                    'field_value' => 'value1',
                                    'expected_value' => 'value1',
                                ]
                            ),
                        ])),
                    ]
                ),
                'expectedClassName' => 'Generated69ef658fb6e99440777d8bbe69f5bc89Test',
                'expectedMethods' => [
                    'setUpBeforeClass' => $this->createExpectedSetUpBeforeClassMethodDefinition('http://example.com'),
                    'testBdc4b8bd83e5660d1c62908dc7a7c43a' => $this->createMethodDefinitionWithDocblock(
                        new MethodDefinition(
                            'testBdc4b8bd83e5660d1c62908dc7a7c43a',
                            CodeBlock::fromContent([
                                '//step one',
                                '//set ".selector" to $data.field_value',
                                '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                                '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                                '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                                '{{ VALUE }} = $field_value ?? null',
                                '{{ VALUE }} = (string) {{ VALUE }}',
                                '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                                '',
                                '//".selector" is $data.expected_value',
                                '{{ EXPECTED }} = $expected_value ?? null',
                                '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                                '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                                '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                                '{{ EXAMINED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                                '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                                '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                                '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                                '',
                            ]),
                            [
                                'expected_value',
                                'field_value',
                            ]
                        ),
                        new DocBlock([
                            new Comment('@dataProvider Bdc4b8bd83e5660d1c62908dc7a7c43aDataProvider'),
                        ])
                    ),
                    'Bdc4b8bd83e5660d1c62908dc7a7c43aDataProvider' => new MethodDefinition(
                        'Bdc4b8bd83e5660d1c62908dc7a7c43aDataProvider',
                        CodeBlock::fromContent([
                            "return [
    '0' => [
        'field_value' => 'value1',
        'expected_value' => 'value1',
    ],
]",
                        ])
                    )
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        'VALUE',
                    ])),
            ],
        ];
    }

    private function createExpectedSetUpBeforeClassMethodDefinition(string $requestUrl): MethodDefinitionInterface
    {
        $method = new MethodDefinition('setUpBeforeClass', CodeBlock::fromContent([
            'parent::setUpBeforeClass()',
            '{{ CLIENT }}->request(\'GET\', \'' . $requestUrl . '\')',
        ]));
        $method->setReturnType('void');
        $method->setStatic();

        return $method;
    }

    private function createMethodDefinitionWithDocblock(
        MethodDefinitionInterface $methodDefinition,
        DocBlock $docBlock
    ): MethodDefinitionInterface {
        $methodDefinition->setDocBlock($docBlock);

        return $methodDefinition;
    }
}
