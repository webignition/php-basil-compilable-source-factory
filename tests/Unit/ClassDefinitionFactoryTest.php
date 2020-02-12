<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSourceFactory\ArrayStatementFactory;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
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
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\DomElementIdentifier\ElementIdentifier;

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
        $this->markTestSkipped();

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
        $testParser = TestParser::create();

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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                ])->withPath('test.yml'),
                'expectedMethodCount' => 1,
                'expectedSetUpBeforeClassMethod' => $this->createExpectedSetUpBeforeClassMethodDefinition(
                    'http://example.com',
                    'test.yml'
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                    'step one' => [
                        'actions' => [
                            'click $".selector"',
                        ],
                        'assertions' => [
                            '$page.title is "value"',
                        ],
                    ],
                ])->withPath('test.yml'),
                'expectedMethodCount' => 2,
                'expectedSetUpBeforeClassMethod' => $this->createExpectedSetUpBeforeClassMethodDefinition(
                    'http://example.com',
                    'test.yml'
                ),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
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
                        VariableNames::STATEMENT,
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                    'step one' => [
                        'actions' => [
                            'set $".selector" to $data.field_value',
                        ],
                        'assertions' => [
                            '$".selector" is $data.expected_value',
                        ],
                        'data' => [
                            '0' => [
                                'field_value' => 'value1',
                                'expected_value' => 'value1',
                            ],
                        ],
                    ],
                ])->withPath('test.yml'),
                'expectedMethodCount' => 3,
                'expectedSetUpBeforeClassMethod' => $this->createExpectedSetUpBeforeClassMethodDefinition(
                    'http://example.com',
                    'test.yml'
                ),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
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
                        VariableNames::STATEMENT,
                    ])),
            ],
        ];
    }

    private function createExpectedSetUpBeforeClassMethodDefinition(
        string $requestUrl,
        string $testPath
    ): MethodDefinitionInterface {
        $method = new MethodDefinition('setUpBeforeClass', CodeBlock::fromContent([
            'parent::setUpBeforeClass()',
            '{{ CLIENT }}->request(\'GET\', \'' . $requestUrl . '\')',
            'self::setBasilTestPath(\'' . $testPath . '\')',
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
            $stepMethodNameFactory,
            SingleQuotedStringEscaper::create()
        );
    }
}
