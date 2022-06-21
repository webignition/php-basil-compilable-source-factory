<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilModels\Model\Test\NamedTest;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Parser\Test\TestParser;
use webignition\SymfonyPantherWebServerRunner\Options;

class ClassDefinitionFactoryTest extends AbstractBrowserTestCase
{
    private ClassDefinitionFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ClassDefinitionFactory::createFactory();
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(NamedTestInterface $test): void
    {
        $classDefinition = $this->factory->createClassDefinition($test, AbstractGeneratedTestCase::class);
        $classCode = $this->testCodeGenerator->createBrowserTestForClass($classDefinition);

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

    /**
     * @return array<mixed>
     */
    public function createSourceDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'single step with single action and single assertion' => [
                'test' => new NamedTest(
                    $testParser->parse([
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
                    ]),
                    'test.yml'
                )
            ],
            'multi-step' => [
                'test' => new NamedTest(
                    $testParser->parse([
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
                    ]),
                    'test.yml'
                )
            ],
            'with data set collection' => [
                'test' => new NamedTest(
                    $testParser->parse([
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
                    ]),
                    'test.yml'
                )
            ],
        ];
    }
}
