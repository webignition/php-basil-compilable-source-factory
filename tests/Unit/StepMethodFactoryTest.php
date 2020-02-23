<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\ArrayExpressionFactory;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\StepMethodFactory;
use webignition\BasilCompilableSourceFactory\StepMethodNameFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StepMethodNameFactoryFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\StepParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class StepMethodFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createStepMethodsDataProvider
     */
    public function testCreateStepMethods(
        StepMethodNameFactory $stepMethodNameFactory,
        string $stepName,
        StepInterface $step,
        string $expectedRenderedTestMethod,
        MetadataInterface $expectedTestMethodMetadata,
        ?string $expectedRenderedDataProviderMethod
    ) {
        $factory = new StepMethodFactory(
            StepHandler::createHandler(),
            ArrayExpressionFactory::createFactory(),
            $stepMethodNameFactory,
            SingleQuotedStringEscaper::create()
        );

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
                'expectedRenderedTestMethod' =>
                    'public function testMethodName()' . "\n"  .
                    '{' . "\n" .
                    '    {{ PHPUNIT }}->setBasilStepName(\'Step Name\');' . "\n" .
                    '}'
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' => null
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
                'expectedRenderedTestMethod' =>
                    'public function testMethodName()' . "\n"  .
                    '{' . "\n" .
                    '    {{ PHPUNIT }}->setBasilStepName(\'step name \\\'contains\\\' single quotes\');' . "\n" .
                    '}'
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' => null
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
                'expectedRenderedTestMethod' =>
                    'public function testMethodName()' . "\n"  .
                    '{' . "\n" .
                    '    {{ PHPUNIT }}->setBasilStepName(\'Step Name\');' . "\n" .
                    "\n" .
                    '    // $".selector" exists <- click $".selector"' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '    {{ PHPUNIT }}->assertTrue(' . "\n" .
                    '        {{ HAS }},' . "\n" .
                    '        \'{' . "\n" .
                    '        "assertion": {' . "\n" .
                    '            "source": "$\\\".selector\\\" exists",' . "\n" .
                    '            "identifier": "$\\\".selector\\\"",' . "\n" .
                    '            "comparison": "exists"' . "\n" .
                    '        }' . "\n" .
                    '    }\'' . "\n" .
                    '    );' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '    // click $".selector"' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '    {{ ELEMENT }}->click();' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '    // $page.title is "value"' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '    {{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null;' . "\n" .
                    '    {{ PHPUNIT }}->assertEquals(' . "\n" .
                    '        {{ EXPECTED }},' . "\n" .
                    '        {{ EXAMINED }}' . "\n" .
                    '    );' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    '}'
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                        'ELEMENT',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' => null
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
                'expectedRenderedTestMethod' =>
                    '/**' . "\n" .
                    ' * @dataProvider dataProviderMethodName' . "\n" .
                    ' */' . "\n" .
                    'public function testMethodName($expected_value, $field_value)' . "\n"  .
                    '{' . "\n" .
                    '    {{ PHPUNIT }}->setBasilStepName(\'Step Name\');' . "\n" .
                    "\n" .
                    '    // $".selector" exists <- set $".selector" to $data.field_value' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ HAS }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '    {{ PHPUNIT }}->assertTrue(' . "\n" .
                    '        {{ HAS }},' . "\n" .
                    '        \'{' . "\n" .
                    '        "assertion": {' . "\n" .
                    '            "source": "$\\\".selector\\\" exists",' . "\n" .
                    '            "identifier": "$\\\".selector\\\"",' . "\n" .
                    '            "comparison": "exists"' . "\n" .
                    '        }' . "\n" .
                    '    }\'' . "\n" .
                    '    );' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '    // set $".selector" to $data.field_value' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAction(\'set $".selector" to $data.field_value\');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '    {{ VALUE }} = $field_value;' . "\n" .
                    '    {{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '    // $".selector" exists <- $".selector" is $data.expected_value' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ HAS }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '    {{ PHPUNIT }}->assertTrue(' . "\n" .
                    '        {{ HAS }},' . "\n" .
                    '        \'{' . "\n" .
                    '        "assertion": {' . "\n" .
                    '            "source": "$\\\".selector\\\" exists",' . "\n" .
                    '            "identifier": "$\\\".selector\\\"",' . "\n" .
                    '            "comparison": "exists"' . "\n" .
                    '        }' . "\n" .
                    '    }\'' . "\n" .
                    '    );' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '    // $".selector" is $data.expected_value' . "\n" .
                    '    {{ STATEMENT }} = Statement::createAssertion(' .
                    '\'$".selector" is $data.expected_value\'' .
                    ');' . "\n" .
                    '    {{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '    {{ EXPECTED }} = $expected_value ?? null;' . "\n" .
                    '    {{ EXAMINED }} = (function () {' . "\n" .
                    '        {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '        return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '    })();' . "\n" .
                    '    {{ PHPUNIT }}->assertEquals(' . "\n" .
                    '        {{ EXPECTED }},' . "\n" .
                    '        {{ EXAMINED }}' . "\n" .
                    '    );' . "\n" .
                    '    {{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    '}'
                ,
                'expectedTestMethodMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                        'ELEMENT',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                        'COLLECTION',
                        'VALUE',
                    ]),
                ]),
                'expectedRenderedDataProviderMethod' =>
                    'public function dataProviderMethodName()' . "\n" .
                    '{' . "\n" .
                    '    return [' . "\n" .
                    '        \'0\' => [' . "\n" .
                    '            \'expected_value\' => \'value1\',' . "\n" .
                    '            \'field_value\' => \'value1\',' . "\n" .
                    '        ],' . "\n" .
                    '    ];' . "\n" .
                    '}'
                ,
            ],
        ];
    }
}
