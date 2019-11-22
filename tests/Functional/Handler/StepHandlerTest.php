<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilActionGenerator\ActionGenerator;
use webignition\BasilAssertionGenerator\AssertionGenerator;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;

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
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        string $fixture,
        StepInterface $step,
        ?CodeBlockInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
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

    public function createSourceDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();
        $assertionGenerator = AssertionGenerator::createGenerator();

        return [
            'single click action' => [
                'fixture' => '/action-click-submit.html',
                'model' => new Step(
                    [
                        $actionGenerator->generate('click "#link-to-index"'),
                    ],
                    []
                ),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                ],
            ],
            'single is assertion' => [
                'fixture' => '/assertions.html',
                'model' => new Step(
                    [],
                    [
                        $assertionGenerator->generate('".selector" is ".selector content"')
                    ]
                ),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'single click action, single assertion' => [
                'fixture' => '/action-click-submit.html',
                'model' => new Step(
                    [
                        $actionGenerator->generate('click "#link-to-index"'),
                    ],
                    [
                        $assertionGenerator->generate(
                            '$page.title is "Test fixture web server default document"'
                        )
                    ]
                ),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'multiple actions, multiple assertions' => [
                'fixture' => '/form.html',
                'model' => new Step(
                    [
                        $actionGenerator->generate(
                            'click "input[name=radio-not-checked][value=not-checked-2]"'
                        ),
                        $actionGenerator->generate(
                            'click "input[name=radio-checked][value=checked-3]"'
                        ),
                    ],
                    [
                        $assertionGenerator->generate(
                            '"input[name=radio-not-checked]" is "not-checked-2"'
                        ),
                        $assertionGenerator->generate(
                            '"input[name=radio-checked]" is "checked-3"'
                        ),
                    ]
                ),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
