<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Test\Configuration;
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
        TestInterface $test,
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                ])->withPath('test.yml'),
                'expectedRenderedClassDefinition' => 'use webignition\BaseBasilTestCase\ClientManager;' . "\n" .
                    'use webignition\BasilModels\Model\Test\Configuration;' . "\n" .
                    "\n" .
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        try {' . "\n" .
                    '            self::setClientManager(new ClientManager(' . "\n" .
                    '                new Configuration(' . "\n" .
                    '                    \'chrome\',' . "\n" .
                    '                    \'http://example.com\'' . "\n" .
                    '                )' . "\n" .
                    '            ));' . "\n" .
                    '            parent::setUpBeforeClass();' . "\n" .
                    '            {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        } catch (\Throwable $exception) {' . "\n" .
                    '            self::staticSetLastException($exception);' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    '}',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(Configuration::class),
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'firefox',
                        'url' => 'http://example.com',
                    ],
                ])->withPath('test.yml'),
                'expectedRenderedClassDefinition' => 'use webignition\BaseBasilTestCase\ClientManager;' . "\n" .
                    'use webignition\BasilModels\Model\Test\Configuration;' . "\n" .
                    "\n" .
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        try {' . "\n" .
                    '            self::setClientManager(new ClientManager(' . "\n" .
                    '                new Configuration(' . "\n" .
                    '                    \'firefox\',' . "\n" .
                    '                    \'http://example.com\'' . "\n" .
                    '                )' . "\n" .
                    '            ));' . "\n" .
                    '            parent::setUpBeforeClass();' . "\n" .
                    '            {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        } catch (\Throwable $exception) {' . "\n" .
                    '            self::staticSetLastException($exception);' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    '}',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(Configuration::class),
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'unknown',
                        'url' => 'http://example.com',
                    ],
                ])->withPath('test.yml'),
                'expectedRenderedClassDefinition' => 'use webignition\BaseBasilTestCase\ClientManager;' . "\n" .
                    'use webignition\BasilModels\Model\Test\Configuration;' . "\n" .
                    "\n" .
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        try {' . "\n" .
                    '            self::setClientManager(new ClientManager(' . "\n" .
                    '                new Configuration(' . "\n" .
                    '                    \'unknown\',' . "\n" .
                    '                    \'http://example.com\'' . "\n" .
                    '                )' . "\n" .
                    '            ));' . "\n" .
                    '            parent::setUpBeforeClass();' . "\n" .
                    '            {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        } catch (\Throwable $exception) {' . "\n" .
                    '            self::staticSetLastException($exception);' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    '}',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(Configuration::class),
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                    'step one' => [],
                ])->withPath('test.yml'),
                'expectedRenderedClassDefinition' => 'use webignition\BaseBasilTestCase\ClientManager;' . "\n" .
                    'use webignition\BasilModels\Model\Test\Configuration;' . "\n" .
                    "\n" .
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        try {' . "\n" .
                    '            self::setClientManager(new ClientManager(' . "\n" .
                    '                new Configuration(' . "\n" .
                    '                    \'chrome\',' . "\n" .
                    '                    \'http://example.com\'' . "\n" .
                    '                )' . "\n" .
                    '            ));' . "\n" .
                    '            parent::setUpBeforeClass();' . "\n" .
                    '            {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        } catch (\Throwable $exception) {' . "\n" .
                    '            self::staticSetLastException($exception);' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    "\n" .
                    '    public function test1()' . "\n" .
                    '    {' . "\n" .
                    "        if (self::hasException()) {\n" .
                    "            return;\n" .
                    "        }\n" .
                    '        {{ PHPUNIT }}->setBasilStepName(\'step one\');' . "\n" .
                    '        {{ PHPUNIT }}->setCurrentDataSet(null);' . "\n" .
                    '    }' . "\n" .
                    '}',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(Configuration::class),
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
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'http://example.com',
                    ],
                    'step one' => [],
                    'step two' => [],
                ])->withPath('test.yml'),
                'expectedRenderedClassDefinition' => 'use webignition\BaseBasilTestCase\ClientManager;' . "\n" .
                    'use webignition\BasilModels\Model\Test\Configuration;' . "\n" .
                    "\n" .
                    'class GeneratedClassName' . "\n" .
                    '{' . "\n" .
                    '    public static function setUpBeforeClass(): void' . "\n" .
                    '    {' . "\n" .
                    '        try {' . "\n" .
                    '            self::setClientManager(new ClientManager(' . "\n" .
                    '                new Configuration(' . "\n" .
                    '                    \'chrome\',' . "\n" .
                    '                    \'http://example.com\'' . "\n" .
                    '                )' . "\n" .
                    '            ));' . "\n" .
                    '            parent::setUpBeforeClass();' . "\n" .
                    '            {{ CLIENT }}->request(\'GET\', \'http://example.com\');' . "\n" .
                    '        } catch (\Throwable $exception) {' . "\n" .
                    '            self::staticSetLastException($exception);' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    "\n" .
                    '    public function test1()' . "\n" .
                    '    {' . "\n" .
                    "        if (self::hasException()) {\n" .
                    "            return;\n" .
                    "        }\n" .
                    '        {{ PHPUNIT }}->setBasilStepName(\'step one\');' . "\n" .
                    '        {{ PHPUNIT }}->setCurrentDataSet(null);' . "\n" .
                    '    }' . "\n" .
                    "\n" .
                    '    public function test2()' . "\n" .
                    '    {' . "\n" .
                    "        if (self::hasException()) {\n" .
                    "            return;\n" .
                    "        }\n" .
                    '        {{ PHPUNIT }}->setBasilStepName(\'step two\');' . "\n" .
                    '        {{ PHPUNIT }}->setCurrentDataSet(null);' . "\n" .
                    '    }' . "\n" .
                    '}',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(Configuration::class),
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
