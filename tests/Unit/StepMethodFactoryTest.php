<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSourceFactory\ArrayStatementFactory;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\StepMethodNameFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StepMethodNameFactoryFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\StepParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class StepMethodFactoryTest extends AbstractTestCase
{
    /**
     * @dataProvider createStepMethodsDataProvider
     */
    public function testCreateStepMethods(
        StepMethodNameFactory $stepMethodNameFactory,
        string $stepName,
        StepInterface $step,
        MethodDefinitionInterface $expectedTestMethod,
        MetadataInterface $expectedTestMethodMetadata,
        ?MethodDefinitionInterface $expectedDataProviderMethod
    ) {
        $factory = new StepMethodFactory(
            StepHandler::createHandler(),
            ArrayStatementFactory::createFactory(),
            $stepMethodNameFactory,
            SingleQuotedStringEscaper::create()
        );

        $stepMethods = $factory->createStepMethods($stepName, $step);

        $testMethod = $stepMethods->getTestMethod();
        $this->assertMethodEquals($expectedTestMethod, $testMethod);
        $this->assertMetadataEquals($expectedTestMethodMetadata, $testMethod->getMetadata());

        $dataProviderMethod = $stepMethods->getDataProviderMethod();

        if (
            $dataProviderMethod instanceof MethodDefinitionInterface &&
            $expectedDataProviderMethod instanceof MethodDefinitionInterface
        ) {
            $this->assertMethodEquals($expectedDataProviderMethod, $dataProviderMethod);
        } else {
            $this->assertNull($dataProviderMethod);
        }
    }

    public function createStepMethodsDataProvider(): array
    {
        $stepParser = StepParser::create();
        $stepMethodNameFactoryFactory = new StepMethodNameFactoryFactory();

        return [
            'empty test' => [
                'stepMethodNameFactory' => $stepMethodNameFactoryFactory->create(
                    [
                        'Step Name' => [
                            'testMethodName',
                        ],
                    ],
                    []
                ),
                'stepName' => 'Step Name',
                'step' => $stepParser->parse([]),
                'expectedTestMethod' => new MethodDefinition('testMethodName', CodeBlock::fromContent([
                    '{{ PHPUNIT }}->setBasilStepName(\'Step Name\')',
                    '',
                ])),
                'expectedTestMethodMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ])),
                'expectedDataProviderMethod' => null
            ],
            'empty test, step name contains single quotes' => [
                'stepMethodNameFactory' => $stepMethodNameFactoryFactory->create(
                    [
                        "step name 'contains' single quotes" => [
                            'testMethodName',
                        ],
                    ],
                    []
                ),
                'stepName' => 'step name \'contains\' single quotes',
                'step' => $stepParser->parse([]),
                'expectedTestMethod' => new MethodDefinition('testMethodName', CodeBlock::fromContent([
                    '{{ PHPUNIT }}->setBasilStepName(\'step name \\\'contains\\\' single quotes\')',
                    '',
                ])),
                'expectedTestMethodMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ])),
                'expectedDataProviderMethod' => null
            ],
            'single step with single action and single assertion' => [
                'stepMethodNameFactory' => $stepMethodNameFactoryFactory->create(
                    [
                        'Step Name' => [
                            'testMethodName',
                        ],
                    ],
                    []
                ),
                'stepName' => 'Step Name',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                    'assertions' => [
                        '$page.title is "value"',
                    ],
                ]),
                'expectedTestMethod' => new MethodDefinition('testMethodName', CodeBlock::fromContent([
                    '{{ PHPUNIT }}->setBasilStepName(\'Step Name\')',
                    '',
                    '// $".selector" exists <- click $".selector"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// click $".selector"',
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ ELEMENT }}->click()',
                    '',
                    '// $page.title is "value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ])),
                'expectedTestMethodMetadata' => (new Metadata())
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
                'expectedDataProviderMethod' => null,
            ],
            'single step with single action and single assertion with data provider' => [
                'stepMethodNameFactory' => $stepMethodNameFactoryFactory->create(
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
                'stepName' => 'Step Name',
                'step' => $stepParser->parse([
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
                ]),
                'expectedTestMethod' => $this->createMethodDefinitionWithDocblock(
                    new MethodDefinition(
                        'testMethodName',
                        CodeBlock::fromContent([
                            '{{ PHPUNIT }}->setBasilStepName(\'Step Name\')',
                            '',
                            '//$".selector" exists <- set $".selector" to $data.field_value',
                            '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                            '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                            '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                            ')',
                            '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                            '',
                            '// set $".selector" to $data.field_value',
                            '{{ STATEMENT }} = Statement::createAction(\'set $".selector" to $data.field_value\')',
                            '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                            '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                            ')',
                            '{{ VALUE }} = $field_value ?? null',
                            '{{ VALUE }} = (string) {{ VALUE }}',
                            '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                            '',
                            '// $".selector" exists <- $".selector" is $data.expected_value',
                            '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                            '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                            '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                            ')',
                            '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                            '',
                            '// $".selector" is $data.expected_value',
                            '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is $data.expected_value\')',
                            '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                            '{{ EXPECTED }} = $expected_value ?? null',
                            '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                            '{{ EXAMINED }} = {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                            ')',
                            '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                            '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                            '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                            '',
                        ]),
                        [
                            'expected_value',
                            'field_value',
                        ]
                    ),
                    new DocBlock([
                        new Comment('@dataProvider dataProviderMethodName'),
                    ])
                ),
                'expectedTestMethodMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
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
                'expectedDataProviderMethod' => new MethodDefinition(
                    'dataProviderMethodName',
                    CodeBlock::fromContent([
                        "return [
    '0' => [
        'expected_value' => 'value1',
        'field_value' => 'value1',
    ],
]",
                    ])
                ),
            ],
        ];
    }

    private function createMethodDefinitionWithDocblock(
        MethodDefinitionInterface $methodDefinition,
        DocBlock $docBlock
    ): MethodDefinitionInterface {
        $methodDefinition->setDocBlock($docBlock);

        return $methodDefinition;
    }
}
