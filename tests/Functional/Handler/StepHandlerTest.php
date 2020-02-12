<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\StepParser;

class StepHandlerTest extends AbstractBrowserTestCase
{
    /**
     * @var StepHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = StepHandler::createHandler();
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        string $fixture,
        StepInterface $step,
        ?CodeBlockInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $this->markTestSkipped();

        $source = $this->handler->handle($step);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            null,
            $teardownStatements,
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

    public function handleDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'single click action' => [
                'fixture' => '/action-click-submit.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $"#link-to-index"',
                    ],
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
            'single is assertion' => [
                'fixture' => '/assertions.html',
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is ".selector content"',
                    ],
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
            'single click action, single assertion' => [
                'fixture' => '/action-click-submit.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $"#link-to-index"',
                    ],
                    'assertions' => [
                        '$page.title is "Test fixture web server default document"',
                    ],
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
            'multiple actions, multiple assertions' => [
                'fixture' => '/form.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $"input[name=radio-not-checked][value=not-checked-2]"',
                        'click $"input[name=radio-checked][value=checked-3]"',
                    ],
                    'assertions' => [
                        '$"input[name=radio-not-checked]" is "not-checked-2"',
                        '$"input[name=radio-checked]" is "checked-3"',
                    ],
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
        ];
    }

    /**
     * @dataProvider handleForFailingActionsDataProvider
     */
    public function testHandleForFailingActions(
        string $fixture,
        StepInterface $step,
        string $expectedExpectationFailedExceptionMessage,
        ?CodeBlockInterface $additionalSetupStatements = null,
        ?CodeBlockInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $this->markTestSkipped();

        $source = $this->handler->handle($step);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            $additionalSetupStatements,
            $teardownStatements,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode, 1);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );

            $this->assertStringContainsString(
                $expectedExpectationFailedExceptionMessage,
                $testRunJob->getOutputAsString()
            );
        }
    }

    public function handleForFailingActionsDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'wait action, element identifier examined value, element does not exist' => [
                'fixture' => '/action-wait.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'wait $".non-existent"',
                    ],
                ]),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
            'wait, attribute identifier examined value, element does not exist' => [
                'fixture' => '/action-wait.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'wait $".non-existent".attribute_name',
                    ],
                ]),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::STATEMENT => ResolvedVariableNames::STATEMENT_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
