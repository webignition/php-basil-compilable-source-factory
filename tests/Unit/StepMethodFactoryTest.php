<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\ArrayStatementFactory;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
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
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\DomElementLocator\ElementLocator;

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
            $stepMethodNameFactory
        );

        $stepMethods = $factory->createStepMethods($stepName, $step);

        $testMethod = $stepMethods->getTestMethod();
        $this->assertMethodEquals($expectedTestMethod, $testMethod);
        $this->assertMetadataEquals($expectedTestMethodMetadata, $testMethod->getMetadata());

        $dataProviderMethod = $stepMethods->getDataProviderMethod();
        if (null === $expectedDataProviderMethod) {
            $this->assertNull($dataProviderMethod);
        } else {
            $this->assertMethodEquals($expectedDataProviderMethod, $dataProviderMethod);
        }
    }

    public function createStepMethodsDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();
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
                'step' => new Step([], []),
                'expectedTestMethod' => new MethodDefinition('testMethodName', CodeBlock::fromContent([
                    '// Step Name',
                ])),
                'expectedTestMethodMetadata' => new Metadata(),
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
                'step' => new Step(
                    [
                        $actionFactory->createFromActionString('click ".selector"'),
                    ],
                    [
                        $assertionFactory->createFromAssertionString('$page.title is "value"'),
                    ]
                ),
                'expectedTestMethod' => new MethodDefinition('testMethodName', CodeBlock::fromContent([
                    '// Step Name',
                    '// click ".selector"',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ ELEMENT }}->click()',
                    '',
                    '// $page.title is "value"',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ])),
                'expectedTestMethodMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
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
                'step' => (new Step(
                    [
                        $actionFactory->createFromActionString('set ".selector" to $data.field_value'),
                    ],
                    [
                        $assertionFactory->createFromAssertionString('".selector" is $data.expected_value'),
                    ]
                ))->withDataSetCollection(new DataSetCollection([
                    new DataSet(
                        '0',
                        [
                            'field_value' => 'value1',
                            'expected_value' => 'value1',
                        ]
                    ),
                ])),
                'expectedTestMethod' => $this->createMethodDefinitionWithDocblock(
                    new MethodDefinition(
                        'testMethodName',
                        CodeBlock::fromContent([
                            '// Step Name',
                            '// set ".selector" to $data.field_value',
                            '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                            '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                            '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                            '{{ VALUE }} = $field_value ?? null',
                            '{{ VALUE }} = (string) {{ VALUE }}',
                            '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                            '',
                            '// ".selector" is $data.expected_value',
                            '{{ EXPECTED }} = $expected_value ?? null',
                            '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                            '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                            '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                            '{{ EXAMINED }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
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
                        new ClassDependency(ElementLocator::class),
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
