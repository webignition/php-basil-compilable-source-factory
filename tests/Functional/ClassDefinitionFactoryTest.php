<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use webignition\BasePantherTestCase\Options;
use webignition\BasilActionGenerator\ActionGenerator;
use webignition\BasilAssertionGenerator\AssertionGenerator;
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
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;

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
        $actionGenerator = ActionGenerator::createGenerator();
        $assertionGenerator = AssertionGenerator::createGenerator();

        return [
            'single step with single action and single assertion' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', Options::getBaseUri() . '/index.html'),
                    [
                        'verify correct page is open' => new Step(
                            [],
                            [
                                $assertionGenerator->generate(
                                    '$page.url is "' . Options::getBaseUri() . '/index.html"'
                                ),
                                $assertionGenerator->generate(
                                    '$page.title is "Test fixture web server default document"'
                                ),
                            ]
                        ),
                    ]
                ),
                'additionalVariableIdentifiers' => [
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'multi-step' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', Options::getBaseUri() . '/index.html'),
                    [
                        'verify starting page is open' => new Step(
                            [],
                            [
                                $assertionGenerator->generate(
                                    '$page.url is "' . Options::getBaseUri() . '/index.html"'
                                ),
                                $assertionGenerator->generate(
                                    '$page.title is "Test fixture web server default document"'
                                ),
                            ]
                        ),
                        'navigate to form' => new Step(
                            [
                                $actionGenerator->generate('click "#link-to-form"'),
                            ],
                            [
                                $assertionGenerator->generate(
                                    '$page.url is "' . Options::getBaseUri() . '/form.html"'
                                ),
                                $assertionGenerator->generate(
                                    '$page.title is "Form"'
                                ),
                            ]
                        ),
                        'verify select menu starting values' => new Step(
                            [],
                            [
                                $assertionGenerator->generate(
                                    '".select-none-selected" is "none-selected-1"'
                                ),
                                $assertionGenerator->generate(
                                    '".select-has-selected" is "has-selected-2"'
                                ),
                            ]
                        ),
                        'modify select menu starting values' => new Step(
                            [
                                $actionGenerator->generate(
                                    'set ".select-none-selected" to "none-selected-3"'
                                ),
                                $actionGenerator->generate(
                                    'set ".select-has-selected" to "has-selected-1"'
                                ),
                            ],
                            [
                                $assertionGenerator->generate(
                                    '".select-none-selected" is "none-selected-3"'
                                ),
                                $assertionGenerator->generate(
                                    '".select-has-selected" is "has-selected-1"'
                                ),
                            ]
                        ),
                    ]
                ),
                'additionalVariableIdentifiers' => [
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                    'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
                    'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
                ],
            ],
            'with data set collection' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', Options::getBaseUri() . '/form.html'),
                    [
                        'verify form field values' => (new Step(
                            [],
                            [
                                $assertionGenerator->generate(
                                    '"input[name=input-without-value]" is ""'
                                ),
                                $assertionGenerator->generate(
                                    '"input[name=input-with-value]" is "test"'
                                ),
                            ]
                        )),
                        'modify form field values' => (new Step(
                            [
                                $actionGenerator->generate(
                                    'set "input[name=input-without-value]" to $data.field_value'
                                ),
                                $actionGenerator->generate(
                                    'set "input[name=input-with-value]" to $data.field_value'
                                ),
                            ],
                            [
                                $assertionGenerator->generate(
                                    '"input[name=input-without-value]" is $data.expected_value'
                                ),
                                $assertionGenerator->generate(
                                    '"input[name=input-with-value]" is $data.expected_value'
                                ),
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet(
                                '0',
                                [
                                    'field_value' => 'value0',
                                    'expected_value' => 'value0',
                                ]
                            ),
                            new DataSet(
                                '1',
                                [
                                    'field_value' => 'value1',
                                    'expected_value' => 'value1',
                                ]
                            ),
                        ])),
                    ]
                ),
                'additionalVariableIdentifiers' => [
                    'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
