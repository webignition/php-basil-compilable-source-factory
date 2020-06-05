<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\ArrayExpressionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\StepMethodNameFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StepMethodNameFactoryFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\StepParser;

class StepMethodFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createStepMethodsDataProvider
     */
    public function testCreateStepMethods(
        string $stepName,
        StepInterface $step,
        StepMethodFactory $factory,
        string $expectedRenderedTestMethod,
        MetadataInterface $expectedTestMethodMetadata,
        ?string $expectedRenderedDataProviderMethod
    ) {
        $stepMethods = $factory->createStepMethods($stepName, $step);

        $testMethod = $stepMethods->getTestMethod();

        $this->assertSame($expectedRenderedTestMethod, $testMethod->render());

        $this->assertNull($testMethod->getReturnType());
        $this->assertFalse($testMethod->isStatic());
        $this->assertSame('public', $testMethod->getVisibility());

        $this->assertEquals($expectedTestMethodMetadata, $testMethod->getMetadata());

        $dataProviderMethod = $stepMethods->getDataProviderMethod();

        if ($dataProviderMethod instanceof MethodDefinitionInterface) {
            $this->assertSame($expectedRenderedDataProviderMethod, $dataProviderMethod->render());
        } else {
            $this->assertNull($dataProviderMethod);
        }
    }

    public function createStepMethodsDataProvider(): array
    {
        $stepParser = StepParser::create();
        $stepMethodNameFactoryFactory = new StepMethodNameFactoryFactory();

        $emptyStep = $stepParser->parse([]);
        $nonEmptyStep = $stepParser->parse([
            'actions' => [
                'click $".selector"',
            ],
            'assertions' => [
                '$page.title is "value"',
            ],
        ]);
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
                    'expected_value' => 'value1',
                ],
            ],
        ]);

        return [
            'empty test' => [
                'stepName' => 'Step Name',
                'step' => $emptyStep,
                'stepMethodFactory' => $this->createStepMethodFactory([
                    StepMethodNameFactory::class => $stepMethodNameFactoryFactory->create(
                        [
                            'Step Name' => [
                                'testMethodName',
                            ],
                        ],
                        []
                    ),
                ]),
                'expectedRenderedTestMethod' =>
                    "public function testMethodName()\n"  .
                    "{\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n" .
                    "}"
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' => null
            ],
            'empty test, step name contains single quotes' => [
                'stepName' => 'step name \'contains\' single quotes',
                'step' => $emptyStep,
                'stepMethodFactory' => $this->createStepMethodFactory([
                    StepMethodNameFactory::class => $stepMethodNameFactoryFactory->create(
                        [
                            "step name 'contains' single quotes" => [
                                'testMethodName',
                            ],
                        ],
                        []
                    ),
                ]),
                'expectedRenderedTestMethod' =>
                    "public function testMethodName()\n"  .
                    "{\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('step name \'contains\' single quotes');\n" .
                    "}"
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' => null
            ],
            'non-empty step' => [
                'stepName' => 'Step Name',
                'step' => $nonEmptyStep,
                'stepMethodFactory' => $this->createStepMethodFactory([
                    StepMethodNameFactory::class => $stepMethodNameFactoryFactory->create(
                        [
                            'Step Name' => [
                                'testMethodName',
                            ],
                        ],
                        []
                    ),
                    StepHandler::class => $this->createStepHandler(
                        $nonEmptyStep,
                        new CodeBlock([
                            new SingleLineComment('mocked step handler response'),
                        ])
                    ),
                ]),
                'expectedRenderedTestMethod' =>
                    "public function testMethodName()\n"  .
                    "{\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n" .
                    "\n" .
                    "    // mocked step handler response\n" .
                    "}"
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' => null
            ],
            'non-empty step with data provider' => [
                'stepName' => 'Step Name',
                'step' => $nonEmptyStepWithDataProvider,
                'stepMethodFactory' => $this->createStepMethodFactory([
                    StepMethodNameFactory::class => $stepMethodNameFactoryFactory->create(
                        [
                            'Step Name' => [
                                'testMethodName',
                            ],
                        ],
                        [
                            'Step Name' => [
                                'dataProviderMethodName',
                            ],
                        ]
                    ),
                    StepHandler::class => $this->createStepHandler(
                        $nonEmptyStepWithDataProvider,
                        new CodeBlock([
                            new SingleLineComment('mocked step handler response'),
                        ])
                    ),
                ]),
                'expectedRenderedTestMethod' =>
                    "/**\n" .
                    " * @dataProvider dataProviderMethodName\n" .
                    " */\n" .
                    'public function testMethodName($expected_value, $field_value)' . "\n"  .
                    "{\n" .
                    "    {{ PHPUNIT }}->setBasilStepName('Step Name');\n" .
                    "\n" .
                    "    // mocked step handler response\n" .
                    "}"
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' =>
                    "public function dataProviderMethodName()\n" .
                    "{\n" .
                    "    return [\n" .
                    "        '0' => [\n" .
                    "            'expected_value' => 'value1',\n" .
                    "            'field_value' => 'value1',\n" .
                    "        ],\n" .
                    "    ];\n" .
                    "}"
                ,
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
        $arrayExpressionFactory = $services[ArrayExpressionFactory::class] ?? ArrayExpressionFactory::createFactory();
        $stepMethodNameFactory = $services[StepMethodNameFactory::class] ?? new StepMethodNameFactory();
        $singleQuotedStringEscaper = $services[SingleQuotedStringEscaper::class] ?? SingleQuotedStringEscaper::create();

        return new StepMethodFactory(
            $stepHandler,
            $arrayExpressionFactory,
            $stepMethodNameFactory,
            $singleQuotedStringEscaper
        );
    }

    private function createStepHandler(StepInterface $expectedStep, CodeBlockInterface $return): StepHandler
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
}
