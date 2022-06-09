<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Test\NamedTest;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;

class ClassDefinitionFactoryTest extends AbstractResolvableTest
{
    /**
     * @dataProvider createClassDefinitionDataProvider
     */
    public function testCreateClassDefinition(
        ClassDefinitionFactory $factory,
        string $expectedClassName,
        NamedTestInterface $test,
        string $expectedRenderedClassDefinition,
        MetadataInterface $expectedMetadata
    ): void {
        $classDefinition = $factory->createClassDefinition($test);

        $this->assertEquals($expectedMetadata, $classDefinition->getMetadata());
        $this->assertSame($expectedClassName, $classDefinition->getSignature()->getName());
        $this->assertRenderResolvable($expectedRenderedClassDefinition, $classDefinition);
    }

    /**
     * @return array<mixed>
     */
    public function createClassDefinitionDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'empty test, browser=chrome' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    \Mockery::mock(StepMethodFactory::class)
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new NamedTest(
                    $testParser->parse([
                        'config' => [
                            'browser' => 'chrome',
                            'url' => 'http://example.com',
                        ],
                    ]),
                    'test.yml'
                ),
                'expectedRenderedClassDefinition' => <<< 'EOF'
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            try {
                                self::setClientManager(new ClientManager('chrome'));
                                parent::setUpBeforeClass();
                                {{ CLIENT }}->request('GET', 'http://example.com');
                            } catch (\Throwable $exception) {
                                self::staticSetLastException($exception);
                            }
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ClientManager::class),
                        new ClassName(\Throwable::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'empty test, browser=firefox' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    \Mockery::mock(StepMethodFactory::class)
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new NamedTest(
                    $testParser->parse([
                        'config' => [
                            'browser' => 'firefox',
                            'url' => 'http://example.com',
                        ],
                    ]),
                    'test.yml'
                ),
                'expectedRenderedClassDefinition' => <<< 'EOF'
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            try {
                                self::setClientManager(new ClientManager('firefox'));
                                parent::setUpBeforeClass();
                                {{ CLIENT }}->request('GET', 'http://example.com');
                            } catch (\Throwable $exception) {
                                self::staticSetLastException($exception);
                            }
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ClientManager::class),
                        new ClassName(\Throwable::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'empty test, browser=unknown' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    \Mockery::mock(StepMethodFactory::class)
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new NamedTest(
                    $testParser->parse([
                        'config' => [
                            'browser' => 'unknown',
                            'url' => 'http://example.com',
                        ],
                    ]),
                    'test.yml'
                ),
                'expectedRenderedClassDefinition' => <<< 'EOF'
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            try {
                                self::setClientManager(new ClientManager('unknown'));
                                parent::setUpBeforeClass();
                                {{ CLIENT }}->request('GET', 'http://example.com');
                            } catch (\Throwable $exception) {
                                self::staticSetLastException($exception);
                            }
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ClientManager::class),
                        new ClassName(\Throwable::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'single empty step' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    StepMethodFactory::createFactory()
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new NamedTest(
                    $testParser->parse([
                        'config' => [
                            'browser' => 'chrome',
                            'url' => 'http://example.com',
                        ],
                        'step one' => [],
                    ]),
                    'test.yml'
                ),
                'expectedRenderedClassDefinition' => <<< 'EOF'
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            try {
                                self::setClientManager(new ClientManager('chrome'));
                                parent::setUpBeforeClass();
                                {{ CLIENT }}->request('GET', 'http://example.com');
                            } catch (\Throwable $exception) {
                                self::staticSetLastException($exception);
                            }
                        }

                        public function test1()
                        {
                            if (self::hasException()) {
                                return;
                            }
                            {{ PHPUNIT }}->setBasilStepName('step one');
                            {{ PHPUNIT }}->setCurrentDataSet(null);
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ClientManager::class),
                        new ClassName(\Throwable::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'two empty steps' => [
                'classDefinitionFactory' => $this->createClassDefinitionFactory(
                    $this->createClassNameFactory('GeneratedClassName'),
                    StepMethodFactory::createFactory()
                ),
                'expectedClassName' => 'GeneratedClassName',
                'test' => new NamedTest(
                    $testParser->parse([
                        'config' => [
                            'browser' => 'chrome',
                            'url' => 'http://example.com',
                        ],
                        'step one' => [],
                        'step two' => [],
                    ]),
                    'test.yml'
                ),
                'expectedRenderedClassDefinition' => <<< 'EOF'
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            try {
                                self::setClientManager(new ClientManager('chrome'));
                                parent::setUpBeforeClass();
                                {{ CLIENT }}->request('GET', 'http://example.com');
                            } catch (\Throwable $exception) {
                                self::staticSetLastException($exception);
                            }
                        }

                        public function test1()
                        {
                            if (self::hasException()) {
                                return;
                            }
                            {{ PHPUNIT }}->setBasilStepName('step one');
                            {{ PHPUNIT }}->setCurrentDataSet(null);
                        }

                        public function test2()
                        {
                            if (self::hasException()) {
                                return;
                            }
                            {{ PHPUNIT }}->setBasilStepName('step two');
                            {{ PHPUNIT }}->setCurrentDataSet(null);
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ClientManager::class),
                        new ClassName(\Throwable::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }

    private function createClassDefinitionFactory(
        ClassNameFactory $classNameFactory,
        StepMethodFactory $stepMethodFactory
    ): ClassDefinitionFactory {
        return new ClassDefinitionFactory(
            $classNameFactory,
            $stepMethodFactory,
            ArgumentFactory::createFactory()
        );
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
            ->andReturn($className)
        ;

        return $classNameFactory;
    }
}
