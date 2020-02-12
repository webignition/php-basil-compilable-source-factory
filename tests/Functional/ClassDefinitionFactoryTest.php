<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\SymfonyPantherWebServerRunner\Options;

class ClassDefinitionFactoryTest extends AbstractBrowserTestCase
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var ClassDefinitionFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classGenerator = ClassGenerator::create();
        $this->factory = ClassDefinitionFactory::createFactory();
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(TestInterface $test, array $additionalVariableIdentifiers = [])
    {
        $this->markTestSkipped();

        $classDefinition = $this->factory->createClassDefinition($test);

        $setupBeforeClassMethod = $classDefinition->getMethod('setUpBeforeClass');
        if ($setupBeforeClassMethod instanceof MethodDefinitionInterface) {
            $setupBeforeClassMethod->addLine(new Statement(
                '// Test harness addition for generating base test use statement',
                (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(AbstractGeneratedTestCase::class),
                    ]))
            ));
        }

        $classCode = $this->testCodeGenerator->createBrowserTestForClass(
            $classDefinition,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );
        }
    }

    public function createSourceDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'single step with single action and single assertion' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => Options::getBaseUri() . '/index.html',
                    ],
                    'verify correct page is open' => [
                        'assertions' => [
                            '$page.url is "' . Options::getBaseUri() . '/index.html"',
                            '$page.title is "Test fixture web server default document"',
                        ],
                    ],
                ])->withPath('test.yml'),
                'additionalVariableIdentifiers' => [
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
            'multi-step' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => Options::getBaseUri() . '/index.html',
                    ],
                    'verify starting page is open' => [
                        'assertions' => [
                            '$page.url is "' . Options::getBaseUri() . '/index.html"',
                            '$page.title is "Test fixture web server default document"',
                        ],
                    ],
                    'navigate to form' => [
                        'actions' => [
                            'click $"#link-to-form"',
                        ],
                        'assertions' => [
                            '$page.url is "' . Options::getBaseUri() . '/form.html"',
                            '$page.title is "Form"',
                        ],
                    ],
                    'verify select menu initial values' => [
                        'assertions' => [
                            '$".select-none-selected" is "none-selected-1"',
                            '$".select-has-selected" is "has-selected-2"',
                        ],
                    ],
                    'modify select menu values' => [
                        'actions' => [
                            'set $".select-none-selected" to "none-selected-3"',
                            'set $".select-has-selected" to "has-selected-1"',
                        ],
                        'assertions' => [
                            '$".select-none-selected" is "none-selected-3"',
                            '$".select-has-selected" is "has-selected-1"',
                        ],
                    ],
                ])->withPath('test.yml'),
                'additionalVariableIdentifiers' => [
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                    'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
                    'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
            'with data set collection' => [
                'test' => $testParser->parse([
                    'config' => [
                        'browser' => 'chrome',
                        'url' => Options::getBaseUri() . '/form.html',
                    ],
                    'verify form field values' => [
                        'assertions' => [
                            '$"input[name=input-without-value]" is ""',
                            '$"input[name=input-with-value]" is "test"',
                        ],
                    ],
                    'modify form field values' => [
                        'actions' => [
                            'set $"input[name=input-without-value]" to $data.field_value',
                            'set $"input[name=input-with-value]" to $data.field_value',
                        ],
                        'assertions' => [
                            '$"input[name=input-without-value]" is $data.expected_value',
                            '$"input[name=input-with-value]" is $data.expected_value',
                        ],
                        'data' => [
                            '0' => [
                                'field_value' => 'value0',
                                'expected_value' => 'value0',
                            ],
                            '1' => [
                                'field_value' => 'value1',
                                'expected_value' => 'value1',
                            ],
                        ],
                    ],
                ])->withPath('test.yml'),
                'additionalVariableIdentifiers' => [
                    'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
