<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\ResolvablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\ArrayExpressionFactory;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\StepMethodNameFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StepMethodNameFactoryFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class ClassDefinitionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createClassDefinitionDataProvider
     */
    public function testCreateClassDefinition(
        ClassDefinitionFactory $factory,
        string $expectedClassName,
        TestInterface $test,
        int $expectedMethodCount,
        string $expectedRenderedSetUpBeforeClassMethod,
        MetadataInterface $expectedMetadata
    ) {
        $classDefinition = $factory->createClassDefinition($test);

        $this->assertEquals($expectedMetadata, $classDefinition->getMetadata());
        $this->assertSame($expectedClassName, $classDefinition->getName());

        $methods = $classDefinition->getMethods();
        $this->assertCount($expectedMethodCount, $methods);

        $setUpBeforeClassMethod = $methods['setUpBeforeClass'] ?? null;
        $this->assertInstanceOf(MethodDefinitionInterface::class, $setUpBeforeClassMethod);

        if ($setUpBeforeClassMethod instanceof MethodDefinitionInterface) {
            $this->assertSame($expectedRenderedSetUpBeforeClassMethod, $setUpBeforeClassMethod->render());
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
                'expectedRenderedSetUpBeforeClassMethod' =>
                    'public static function setUpBeforeClass(): void' . "\n" .
                    '{' . "\n" .
                    '    parent::setUpBeforeClass();' . "\n" .
                    '    {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '    self::setBasilTestPath(\'test.yml\');' . "\n" .
                    '}'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
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
                'expectedRenderedSetUpBeforeClassMethod' =>
                    'public static function setUpBeforeClass(): void' . "\n" .
                    '{' . "\n" .
                    '    parent::setUpBeforeClass();' . "\n" .
                    '    {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '    self::setBasilTestPath(\'test.yml\');' . "\n" .
                    '}'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ACTION_FACTORY,
                        VariableNames::ASSERTION_FACTORY,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
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
                'expectedRenderedSetUpBeforeClassMethod' =>
                    'public static function setUpBeforeClass(): void' . "\n" .
                    '{' . "\n" .
                    '    parent::setUpBeforeClass();' . "\n" .
                    '    {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '    self::setBasilTestPath(\'test.yml\');' . "\n" .
                    '}'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ACTION_FACTORY,
                        VariableNames::ASSERTION_FACTORY,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
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

    private function createStepMethodFactory(StepMethodNameFactory $stepMethodNameFactory): StepMethodFactory
    {
        return new StepMethodFactory(
            StepHandler::createHandler(),
            ArrayExpressionFactory::createFactory(),
            $stepMethodNameFactory,
            SingleQuotedStringEscaper::create()
        );
    }
}
