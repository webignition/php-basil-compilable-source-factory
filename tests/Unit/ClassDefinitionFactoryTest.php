<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\ArrayStatementFactory;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\StepMethodNameFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StepMethodNameFactoryFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Line\ClassDependency;
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

class ClassDefinitionFactoryTest extends AbstractTestCase
{
    /**
     * @dataProvider createClassDefinitionDataProvider
     */
    public function testCreateClassDefinition(
        ClassDefinitionFactory $factory,
        string $expectedClassName,
        TestInterface $test,
        int $expectedMethodCount,
        MethodDefinitionInterface $expectedSetUpBeforeClassMethod,
        MetadataInterface $expectedMetadata
    ) {
        $classDefinition = $factory->createClassDefinition($test);

        $this->assertMetadataEquals($expectedMetadata, $classDefinition->getMetadata());
        $this->assertSame($expectedClassName, $classDefinition->getName());

        $methods = $classDefinition->getMethods();
        $this->assertCount($expectedMethodCount, $methods);

        $setUpBeforeClassMethod = $methods['setUpBeforeClass'] ?? null;

        $this->assertInstanceOf(MethodDefinitionInterface::class, $setUpBeforeClassMethod);

        if ($setUpBeforeClassMethod instanceof MethodDefinitionInterface) {
            $this->assertMethodEquals($expectedSetUpBeforeClassMethod, $setUpBeforeClassMethod);
            $this->assertMetadataEquals(
                (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
                $setUpBeforeClassMethod->getMetadata()
            );
        }
    }

    public function createClassDefinitionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();
        $stepMethodNameFactoryFactory = new StepMethodNameFactoryFactory();

        return [
            'empty test' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    $this->createStepMethodFactory(
                        $stepMethodNameFactoryFactory->create([], [])
                    )
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
                'expectedMethodCount' => 1,
                'expectedSetUpBeforeClassMethod' => $this->createExpectedSetUpBeforeClassMethodDefinition(
                    'http://example.com'
                ),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'single step with single action and single assertion' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    $this->createStepMethodFactory(
                        $stepMethodNameFactoryFactory->create(
                            [
                                'step one' => [
                                    'testStepOneMethodName',
                                ],
                            ],
                            []
                        )
                    )
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new Test(
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
                'expectedMethodCount' => 2,
                'expectedSetUpBeforeClassMethod' => $this->createExpectedSetUpBeforeClassMethodDefinition(
                    'http://example.com'
                ),
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
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    $this->createStepMethodFactory(
                        $stepMethodNameFactoryFactory->create(
                            [
                                'step one' => [
                                    'testStepOneMethodName',
                                ],
                            ],
                            [
                                'step one' => [
                                    'stepOneDataProviderMethodName',
                                ],
                            ]
                        )
                    )
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new Test(
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
                'expectedMethodCount' => 3,
                'expectedSetUpBeforeClassMethod' => $this->createExpectedSetUpBeforeClassMethodDefinition(
                    'http://example.com'
                ),
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

    private function createClassDefinitionFactory(
        ClassNameFactory $classNameFactory,
        StepMethodFactory $stepMethodFactory
    ): ClassDefinitionFactory {
        return new ClassDefinitionFactory($classNameFactory, $stepMethodFactory);
    }

    private function createClassNameFactory(string $className): ClassNameFactory
    {
        $classNameFactory = \Mockery::mock(ClassNameFactory::class);
        $classNameFactory
            ->shouldReceive('create')
            ->withArgs(function ($test) {
                $this->assertInstanceOf(TestInterface::class, $test);

                return true;
            })
            ->andReturn($className);

        return $classNameFactory;
    }

    private function createStepMethodFactory(StepMethodNameFactory $stepMethodNameFactory): StepMethodFactory
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            ArrayStatementFactory::createFactory(),
            $stepMethodNameFactory
        );
    }
}
