<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodDefinition;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\StepParser;
use webignition\BasilParser\Test\TestParser;

class ClassDefinitionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createClassDefinitionDataProvider
     */
    public function testCreateClassDefinition(
        ClassDefinitionFactory $factory,
        string $expectedClassName,
        TestInterface $test,
        string $expectedRenderedClassDefinition,
        MetadataInterface $expectedMetadata
    ) {
        $classDefinition = $factory->createClassDefinition($test);

        $this->assertEquals($expectedMetadata, $classDefinition->getMetadata());
        $this->assertSame($expectedClassName, $classDefinition->getName());
        $this->assertEquals($expectedRenderedClassDefinition, $classDefinition->render());
    }

    public function createClassDefinitionDataProvider(): array
    {
        $testParser = TestParser::create();
        $stepParser = StepParser::create();

        return [
            'empty test' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    \Mockery::mock(StepMethodFactory::class)
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                ])->withPath('test.yml'),
                'expectedRenderedClassDefinition' =>
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        parent::setUpBeforeClass();' . "\n" .
                    '        {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        self::setBasilTestPath(\'test.yml\');' . "\n" .
                    '    }' . "\n" .
                    '}'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'single step with single action and single assertion' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    $this->createStepMethodFactory(
                        'step one',
                        $stepParser->parse([
                            'actions' => [
                                'click $".selector"',
                            ],
                            'assertions' => [
                                '$page.title is "value"',
                            ],
                        ]),
                        new MethodDefinition('mockedMethodDefinition1', new Body([]), [])
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
                'expectedRenderedClassDefinition' =>
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        parent::setUpBeforeClass();' . "\n" .
                    '        {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        self::setBasilTestPath(\'test.yml\');' . "\n" .
                    '    }' . "\n" .
                    "\n" .
                    '    public function mockedMethodDefinition1()' . "\n" .
                    '    {' . "\n" .
                    "\n" .
                    '    }' . "\n" .
                    '}'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
        ];
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

    private function createStepMethodFactory(
        string $expectedStepName,
        StepInterface $expectedStep,
        MethodDefinitionInterface $return
    ): StepMethodFactory {
        $stepMethodFactory = \Mockery::mock(StepMethodFactory::class);
        $stepMethodFactory
            ->shouldReceive('create')
            ->withArgs(function (string $stepName, StepInterface $step) use ($expectedStepName, $expectedStep) {
                $this->assertSame($expectedStepName, $stepName);
                $this->assertEquals($expectedStep, $step);

                return true;
            })
            ->andReturn($return);

        return $stepMethodFactory;
    }
}
