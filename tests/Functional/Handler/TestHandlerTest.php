<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasePantherTestCase\Options;
use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCompilableSourceFactory\Handler\TestHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;

class TestHandlerTest extends AbstractHandlerTest
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classGenerator = ClassGenerator::create();
    }

    protected function createHandler(): HandlerInterface
    {
        return TestHandler::createHandler();
    }
    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(TestInterface $test, array $additionalVariableIdentifiers = [])
    {
        $source = $this->handler->handle($test);

        $classCode = '';

        if ($source instanceof ClassDefinitionInterface) {
            $setupBeforeClassMethod = $source->getMethod('setUpBeforeClass');
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
                $source,
                $additionalVariableIdentifiers
            );
        }

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
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'single step with single action and single assertion' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', Options::getBaseUri() . '/index.html'),
                    [
                        'verify correct page is open' => new Step(
                            [],
                            [
                                $assertionFactory->createFromAssertionString(
                                    '$page.url is "' . Options::getBaseUri() . '/index.html"'
                                ),
                                $assertionFactory->createFromAssertionString(
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
                                $assertionFactory->createFromAssertionString(
                                    '$page.url is "' . Options::getBaseUri() . '/index.html"'
                                ),
                                $assertionFactory->createFromAssertionString(
                                    '$page.title is "Test fixture web server default document"'
                                ),
                            ]
                        ),
                        'navigate to form' => new Step(
                            [
                                $actionFactory->createFromActionString('click "#link-to-form"'),
                            ],
                            [
                                $assertionFactory->createFromAssertionString(
                                    '$page.url is "' . Options::getBaseUri() . '/form.html"'
                                ),
                                $assertionFactory->createFromAssertionString(
                                    '$page.title is "Form"'
                                ),
                            ]
                        ),
                        'verify select menu starting values' => new Step(
                            [],
                            [
                                $assertionFactory->createFromAssertionString(
                                    '".select-none-selected" is "none-selected-1"'
                                ),
                                $assertionFactory->createFromAssertionString(
                                    '".select-has-selected" is "has-selected-2"'
                                ),
                            ]
                        ),
                        'modify select menu starting values' => new Step(
                            [
                                $actionFactory->createFromActionString(
                                    'set ".select-none-selected" to "none-selected-3"'
                                ),
                                $actionFactory->createFromActionString(
                                    'set ".select-has-selected" to "has-selected-1"'
                                ),
                            ],
                            [
                                $assertionFactory->createFromAssertionString(
                                    '".select-none-selected" is "none-selected-3"'
                                ),
                                $assertionFactory->createFromAssertionString(
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
        ];
    }
}
