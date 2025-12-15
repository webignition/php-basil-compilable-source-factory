<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\Attribute\StepName;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilModels\Model\Test\NamedTest;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilModels\Parser\Test\TestParser;

class ClassDefinitionFactoryTest extends AbstractResolvableTestCase
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
    public static function createClassDefinitionDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'empty test, browser=chrome' => [
                'factory' => self::createClassDefinitionFactory(
                    self::createClassNameFactory('GeneratedClassName'),
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
                            self::setClientManager(new ClientManager('chrome'));
                            parent::setUpBeforeClass();
                            {{ CLIENT }}->request('GET', 'http://example.com');
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClientManager::class,
                    ],
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'empty test, browser=firefox' => [
                'factory' => self::createClassDefinitionFactory(
                    self::createClassNameFactory('GeneratedClassName'),
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
                            self::setClientManager(new ClientManager('firefox'));
                            parent::setUpBeforeClass();
                            {{ CLIENT }}->request('GET', 'http://example.com');
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClientManager::class,
                    ],
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'empty test, browser=unknown' => [
                'factory' => self::createClassDefinitionFactory(
                    self::createClassNameFactory('GeneratedClassName'),
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
                            self::setClientManager(new ClientManager('unknown'));
                            parent::setUpBeforeClass();
                            {{ CLIENT }}->request('GET', 'http://example.com');
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClientManager::class,
                    ],
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'single empty step' => [
                'factory' => self::createClassDefinitionFactory(
                    self::createClassNameFactory('GeneratedClassName'),
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
                    use webignition\BaseBasilTestCase\Attribute\StepName;
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            self::setClientManager(new ClientManager('chrome'));
                            parent::setUpBeforeClass();
                            {{ CLIENT }}->request('GET', 'http://example.com');
                        }

                        #[StepName('step one')]
                        public function test1()
                        {
                            if (self::hasException()) {
                                return;
                            }
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClientManager::class,
                        StepName::class,
                    ],
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'two empty steps' => [
                'factory' => self::createClassDefinitionFactory(
                    self::createClassNameFactory('GeneratedClassName'),
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
                    use webignition\BaseBasilTestCase\Attribute\StepName;
                    use webignition\BaseBasilTestCase\ClientManager;
                    
                    class GeneratedClassName
                    {
                        public static function setUpBeforeClass(): void
                        {
                            self::setClientManager(new ClientManager('chrome'));
                            parent::setUpBeforeClass();
                            {{ CLIENT }}->request('GET', 'http://example.com');
                        }

                        #[StepName('step one')]
                        public function test1()
                        {
                            if (self::hasException()) {
                                return;
                            }
                        }

                        #[StepName('step two')]
                        public function test2()
                        {
                            if (self::hasException()) {
                                return;
                            }
                        }
                    }
                    EOF,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClientManager::class,
                        StepName::class,
                    ],
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
        ];
    }

    private static function createClassDefinitionFactory(
        ClassNameFactory $classNameFactory,
        StepMethodFactory $stepMethodFactory
    ): ClassDefinitionFactory {
        return new ClassDefinitionFactory(
            $classNameFactory,
            $stepMethodFactory,
            ArgumentFactory::createFactory()
        );
    }

    private static function createClassNameFactory(string $className): ClassNameFactory
    {
        $classNameFactory = \Mockery::mock(ClassNameFactory::class);
        $classNameFactory
            ->shouldReceive('create')
            ->withArgs(function ($test) {
                self::assertInstanceOf(TestInterface::class, $test);

                return true;
            })
            ->andReturn($className)
        ;

        return $classNameFactory;
    }
}
