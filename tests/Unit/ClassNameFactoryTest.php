<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Step\StepCollection;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\ConfigurationInterface;
use webignition\BasilModels\Test\Test;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;

class ClassNameFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(TestInterface $test, string $expectedClassName)
    {
        self::assertSame($expectedClassName, (new ClassNameFactory())->create($test));
    }

    public function createDataProvider(): array
    {
        return [
            'path test.yml, browser chrome' => [
                'test' => $this->createTest(
                    'test.yml',
                    $this->createConfiguration('chrome')
                ),
                'expectedClassName' => 'Generated8b691caed69e58a60c79e9d1592a9f6aTest',
            ],
            'path test.yml, browser firefox' => [
                'test' => $this->createTest(
                    'test.yml',
                    $this->createConfiguration('firefox')
                ),
                'expectedClassName' => 'Generated4b8f9518f66bf58061cb0d263ff58430Test',
            ],
            'path /sub/test.yml' => [
                'test' => $this->createTest(
                    '/sub/test.yml',
                    $this->createConfiguration('chrome')
                ),
                'expectedClassName' => 'Generated4ba6193a8ed1a54deca52dd8a2b1fc12Test',
            ],
        ];
    }

    private function createTest(string $path, ConfigurationInterface $configuration): TestInterface
    {
        $test = \Mockery::mock(TestInterface::class);

        $test
            ->shouldReceive('getPath')
            ->andReturn($path);

        $test
            ->shouldReceive('getConfiguration')
            ->andReturn($configuration);

        return $test;
    }

    private function createConfiguration(string $browser): ConfigurationInterface
    {
        $configuration = \Mockery::mock(ConfigurationInterface::class);

        $configuration
            ->shouldReceive('getBrowser')
            ->andReturn($browser);

        return $configuration;
    }
}
