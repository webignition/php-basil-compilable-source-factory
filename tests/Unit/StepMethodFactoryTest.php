<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\SingleLineComment;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\StepParser;

class StepMethodFactoryTest extends \PHPUnit\Framework\TestCase
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
    ) {
        $testMethods = $factory->create($index, $stepName, $step);
        self::assertCount(1, $testMethods);

        $testMethod = $testMethods[0];
        self::assertInstanceOf(MethodDefinitionInterface::class, $testMethod);

        $this->assertTestMethod($expectedRenderedTestMethod, $expectedTestMethodMetadata, $testMethod);
    }

    public function createWithoutDataProviderDataProvider(): array
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
                'stepMethodFactory' => StepMethodFactory::createFactory(),
                'expectedRenderedTestMethod' =>
                    "public function test1()\n" .
                    "{\n" .
                    "    if (self::hasException()) {\n" .
                    "        return;\n" .
                    "    }\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n" .
                    "    {{ PHPUNIT }}->setCurrentDataSet(null);\n" .
                    "}",
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
                'stepMethodFactory' => StepMethodFactory::createFactory(),
                'expectedRenderedTestMethod' =>
                    "public function test2()\n" .
                    "{\n" .
                    "    if (self::hasException()) {\n" .
                    "        return;\n" .
                    "    }\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('step name \'contains\' single quotes');\n" .
                    "    {{ PHPUNIT }}->setCurrentDataSet(null);\n" .
                    "}",
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
                'stepMethodFactory' => $this->createStepMethodFactory([
                    StepHandler::class => $this->createStepHandler(
                        $nonEmptyStep,
                        new Body([
                            new SingleLineComment('mocked step handler response'),
                        ])
                    ),
                ]),
                'expectedRenderedTestMethod' =>
                    "public function test3()\n" .
                    "{\n" .
                    "    if (self::hasException()) {\n" .
                    "        return;\n" .
                    "    }\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n" .
                    "    {{ PHPUNIT }}->setCurrentDataSet(null);\n" .
                    "\n" .
                    "    // mocked step handler response\n" .
                    "}",
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
    ) {
        $testMethods = $factory->create($index, $stepName, $step);
        self::assertCount(2, $testMethods);

        $testMethod = $testMethods[0];
        self::assertInstanceOf(MethodDefinitionInterface::class, $testMethod);
        $this->assertTestMethod($expectedRenderedTestMethod, $expectedTestMethodMetadata, $testMethod);

        $dataProvider = $testMethods[1];
        self::assertInstanceOf(MethodDefinitionInterface::class, $dataProvider);
        $this->assertDataProviderMethod($expectedRenderedDataProvider, $dataProvider);
    }

    public function createWithDataProviderDataProvider(): array
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
                'stepMethodFactory' => $this->createStepMethodFactory([
                    StepHandler::class => $this->createStepHandler(
                        $nonEmptyStepWithDataProvider,
                        new Body([
                            new SingleLineComment('mocked step handler response'),
                        ])
                    ),
                ]),
                'expectedRenderedTestMethod' => "/**\n" .
                    " * @dataProvider dataProvider4\n" .
                    " *\n" .
                    " * @param string " . '$expected_value' . "\n" .
                    " * @param string " . '$field_value' . "\n" .
                    " */\n" .
                    'public function test4($expected_value, $field_value)' . "\n" .
                    "{\n" .
                    "    if (self::hasException()) {\n" .
                    "        return;\n" .
                    "    }\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n" .
                    "    {{ PHPUNIT }}->setCurrentDataSet(DataSet::fromArray([\n" .
                    "        'name' => {{ PHPUNIT }}->dataName(),\n" .
                    "        'data' => [\n" .
                    "            'expected_value' => \$expected_value,\n" .
                    "            'field_value' => \$field_value,\n" .
                    "        ],\n" .
                    "    ]));\n" .
                    "\n" .
                    "    // mocked step handler response\n" .
                    "}",
                'expectedRenderedDataProvider' => "public function dataProvider4(): array\n" .
                    "{\n" .
                    "    return [\n" .
                    "        '0' => [\n" .
                    "            'expected_value' => 'value2',\n" .
                    "            'field_value' => 'value1',\n" .
                    "        ],\n" .
                    "        '1' => [\n" .
                    "            'expected_value' => '\"value4\"',\n" .
                    "            'field_value' => '\"value3\"',\n" .
                    "        ],\n" .
                    "        '2' => [\n" .
                    "            'expected_value' => '\'value6\'',\n" .
                    "            'field_value' => '\'value5\'',\n" .
                    "        ],\n" .
                    "    ];\n" .
                    "}",
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(DataSet::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $services
     *
     * @return StepMethodFactory
     */
    private function createStepMethodFactory(array $services = []): StepMethodFactory
    {
        $stepHandler = $services[StepHandler::class] ?? StepHandler::createHandler();
        $singleQuotedStringEscaper = $services[SingleQuotedStringEscaper::class] ?? SingleQuotedStringEscaper::create();

        return new StepMethodFactory(
            $stepHandler,
            $singleQuotedStringEscaper,
            ArgumentFactory::createFactory()
        );
    }

    private function createStepHandler(StepInterface $expectedStep, BodyInterface $return): StepHandler
    {
        $stepHandler = \Mockery::mock(StepHandler::class);

        $stepHandler
            ->shouldReceive('handle')
            ->withArgs(function (StepInterface $step) use ($expectedStep) {
                $this->assertEquals($expectedStep, $step);

                return true;
            })
            ->andReturn($return);

        return $stepHandler;
    }

    private function assertTestMethod(
        string $expectedRendered,
        MetadataInterface $expectedMetadata,
        MethodDefinitionInterface $testMethod
    ): void {
        $this->assertSame($expectedRendered, $testMethod->render());

        $this->assertNull($testMethod->getReturnType());
        $this->assertFalse($testMethod->isStatic());
        $this->assertSame('public', $testMethod->getVisibility());
        $this->assertEquals($expectedMetadata, $testMethod->getMetadata());
    }

    private function assertDataProviderMethod(
        string $expectedRendered,
        MethodDefinitionInterface $testMethod
    ): void {
        $this->assertSame($expectedRendered, $testMethod->render());

        $this->assertSame('array', $testMethod->getReturnType());
        $this->assertFalse($testMethod->isStatic());
        $this->assertSame('public', $testMethod->getVisibility());
        $this->assertEquals(new Metadata(), $testMethod->getMetadata());
    }
}
