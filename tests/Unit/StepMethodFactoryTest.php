<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\DataSet\DataSet;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\StepParser;

class StepMethodFactoryTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createWithoutDataProviderDataProvider
     */
    public function testCreateWithoutDataProvider(
        int $index,
        string $stepName,
        StepInterface $step,
        StepMethodFactory $factory,
        string $expectedRenderedTestMethod,
        MetadataInterface $expectedTestMethodMetadata
    ): void {
        $testMethods = $factory->create($index, $stepName, $step);
        self::assertCount(1, $testMethods);

        $testMethod = $testMethods[0];
        $this->assertTestMethod($expectedRenderedTestMethod, $expectedTestMethodMetadata, $testMethod);
    }

    /**
     * @return array<mixed>
     */
    public static function createWithoutDataProviderDataProvider(): array
    {
        $stepParser = StepParser::create();

        $emptyStep = $stepParser->parse([]);
        $nonEmptyStep = $stepParser->parse([
            'actions' => [
                'click $".selector"',
            ],
            'assertions' => [
                '$page.title is "value"',
            ],
        ]);

        return [
            'empty test' => [
                'index' => 1,
                'stepName' => 'Step Name',
                'step' => $emptyStep,
                'factory' => StepMethodFactory::createFactory(),
                'expectedRenderedTestMethod' => "public function test1()\n"
                    . "{\n"
                    . "    if (self::hasException()) {\n"
                    . "        return;\n"
                    . "    }\n"
                    . "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n"
                    . "    {{ PHPUNIT }}->setCurrentDataSet(null);\n"
                    . '}',
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'empty test, step name contains single quotes' => [
                'index' => 2,
                'stepName' => 'step name \'contains\' single quotes',
                'step' => $emptyStep,
                'factory' => StepMethodFactory::createFactory(),
                'expectedRenderedTestMethod' => "public function test2()\n"
                    . "{\n"
                    . "    if (self::hasException()) {\n"
                    . "        return;\n"
                    . "    }\n"
                    . "    {{ PHPUNIT }}->setBasilStepName('step name \\'contains\\' single quotes');\n"
                    . "    {{ PHPUNIT }}->setCurrentDataSet(null);\n"
                    . '}',
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'non-empty step' => [
                'index' => 3,
                'stepName' => 'Step Name',
                'step' => $nonEmptyStep,
                'factory' => self::createStepMethodFactory([
                    StepHandler::class => self::createStepHandler(
                        $nonEmptyStep,
                        new Body([
                            new SingleLineComment('mocked step handler response'),
                        ])
                    ),
                ]),
                'expectedRenderedTestMethod' => "public function test3()\n"
                    . "{\n"
                    . "    if (self::hasException()) {\n"
                    . "        return;\n"
                    . "    }\n"
                    . "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n"
                    . "    {{ PHPUNIT }}->setCurrentDataSet(null);\n"
                    . "\n"
                    . "    // mocked step handler response\n"
                    . '}',
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider createWithDataProviderDataProvider
     */
    public function testCreateWithDataProvider(
        int $index,
        string $stepName,
        StepInterface $step,
        StepMethodFactory $factory,
        string $expectedRenderedTestMethod,
        string $expectedRenderedDataProvider,
        MetadataInterface $expectedTestMethodMetadata
    ): void {
        $testMethods = $factory->create($index, $stepName, $step);
        self::assertCount(2, $testMethods);

        $testMethod = $testMethods[0];
        $this->assertTestMethod($expectedRenderedTestMethod, $expectedTestMethodMetadata, $testMethod);

        $dataProvider = $testMethods[1];
        $this->assertDataProviderMethod($expectedRenderedDataProvider, $dataProvider);
    }

    /**
     * @return array<mixed>
     */
    public static function createWithDataProviderDataProvider(): array
    {
        $stepParser = StepParser::create();

        $nonEmptyStepWithDataProvider = $stepParser->parse([
            'actions' => [
                'set $".selector" to $data.field_value',
            ],
            'assertions' => [
                '$".selector" is $data.expected_value',
            ],
            'data' => [
                0 => [
                    'field_value' => 'value1',
                    'expected_value' => 'value2',
                ],
                1 => [
                    'field_value' => '"value3"',
                    'expected_value' => '"value4"',
                ],
                2 => [
                    'field_value' => "'value5'",
                    'expected_value' => "'value6'",
                ],
            ],
        ]);

        return [
            'non-empty step with data provider' => [
                'index' => 4,
                'stepName' => 'Step Name',
                'step' => $nonEmptyStepWithDataProvider,
                'factory' => self::createStepMethodFactory([
                    StepHandler::class => self::createStepHandler(
                        $nonEmptyStepWithDataProvider,
                        new Body([
                            new SingleLineComment('mocked step handler response'),
                        ])
                    ),
                ]),
                'expectedRenderedTestMethod' => <<<'EOD'
                    #[DataProvider('dataProvider4')]
                    public function test4($expected_value, $field_value)
                    {
                        if (self::hasException()) {
                            return;
                        }
                        {{ PHPUNIT }}->setBasilStepName('Step Name');
                        {{ PHPUNIT }}->setCurrentDataSet(DataSet::fromArray([
                            'name' => {{ PHPUNIT }}->dataName(),
                            'data' => [
                                'expected_value' => $expected_value,
                                'field_value' => $field_value,
                            ],
                        ]));
                    
                        // mocked step handler response
                    }
                    EOD,
                'expectedRenderedDataProvider' => <<<'EOD'
                    public function dataProvider4(): array
                    {
                        return [
                            '0' => [
                                'expected_value' => 'value2',
                                'field_value' => 'value1',
                            ],
                            '1' => [
                                'expected_value' => '"value4"',
                                'field_value' => '"value3"',
                            ],
                            '2' => [
                                'expected_value' => '\'value6\'',
                                'field_value' => '\'value5\'',
                            ],
                        ];
                    }
                    EOD,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(DataProvider::class),
                            new ClassName(DataSet::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $services
     */
    private static function createStepMethodFactory(array $services = []): StepMethodFactory
    {
        $stepHandler = $services[StepHandler::class] ?? null;
        $stepHandler = $stepHandler instanceof StepHandler ? $stepHandler : StepHandler::createHandler();

        $singleQuotedStringEscaper = $services[SingleQuotedStringEscaper::class] ?? null;
        $singleQuotedStringEscaper = $singleQuotedStringEscaper instanceof SingleQuotedStringEscaper
            ? $singleQuotedStringEscaper
            : SingleQuotedStringEscaper::create();

        return new StepMethodFactory(
            $stepHandler,
            $singleQuotedStringEscaper,
            ArgumentFactory::createFactory()
        );
    }

    private static function createStepHandler(StepInterface $expectedStep, BodyInterface $return): StepHandler
    {
        $stepHandler = \Mockery::mock(StepHandler::class);

        $stepHandler
            ->shouldReceive('handle')
            ->withArgs(function (StepInterface $step) use ($expectedStep) {
                self::assertEquals($expectedStep, $step);

                return true;
            })
            ->andReturn($return)
        ;

        return $stepHandler;
    }

    private function assertTestMethod(
        string $expectedRendered,
        MetadataInterface $expectedMetadata,
        MethodDefinitionInterface $testMethod
    ): void {
        $this->assertRenderResolvable($expectedRendered, $testMethod);

        $this->assertNull($testMethod->getReturnType());
        $this->assertFalse($testMethod->isStatic());
        $this->assertSame('public', $testMethod->getVisibility());
        $this->assertEquals($expectedMetadata, $testMethod->getMetadata());
    }

    private function assertDataProviderMethod(
        string $expectedRendered,
        MethodDefinitionInterface $testMethod
    ): void {
        $this->assertRenderResolvable($expectedRendered, $testMethod);

        $this->assertSame('array', $testMethod->getReturnType());
        $this->assertFalse($testMethod->isStatic());
        $this->assertSame('public', $testMethod->getVisibility());
        $this->assertEquals(new Metadata(), $testMethod->getMetadata());
    }
}
