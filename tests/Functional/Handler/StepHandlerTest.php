<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;

class StepHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return StepHandler::createHandler();
    }
    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        string $fixture,
        StepInterface $step,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->createSource($step);

        $classCode = $this->testCodeGenerator->createForLineList(
            $source,
            $fixture,
            null,
            $teardownStatements,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);
        $this->testRunner->run($testRunJob);

        $this->assertSame(
            $testRunJob->getExpectedExitCode(),
            $testRunJob->getExitCode(),
            $testRunJob->getOutputAsString()
        );
    }

    public function createSourceDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'single click action' => [
                'fixture' => '/action-click-submit.html',
                'model' => new Step(
                    [
                        $actionFactory->createFromActionString('click "#link-to-index"'),
                    ],
                    []
                ),
                'teardownStatements' => new LineList([
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
                        $assertionFactory->createFromAssertionString('".selector" is ".selector content"')
                    ]
                ),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'single click action, single assertion' => [
                'fixture' => '/action-click-submit.html',
                'model' => new Step(
                    [
                        $actionFactory->createFromActionString('click "#link-to-index"'),
                    ],
                    [
                        $assertionFactory->createFromAssertionString(
                            '$page.title is "Test fixture web server default document"'
                        )
                    ]
                ),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'multiple actions, multiple assertions' => [
                'fixture' => '/form.html',
                'model' => new Step(
                    [
                        $actionFactory->createFromActionString(
                            'click "input[name=radio-not-checked][value=not-checked-2]"'
                        ),
                        $actionFactory->createFromActionString(
                            'click "input[name=radio-checked][value=checked-3]"'
                        ),
                    ],
                    [
                        $assertionFactory->createFromAssertionString(
                            '"input[name=radio-not-checked]" is "not-checked-2"'
                        ),
                        $assertionFactory->createFromAssertionString(
                            '"input[name=radio-checked]" is "checked-3"'
                        ),
                    ]
                ),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
