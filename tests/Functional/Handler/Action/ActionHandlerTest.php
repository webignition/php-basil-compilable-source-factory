<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilModels\Model\Action\ActionInterface;

class ActionHandlerTest extends AbstractBrowserTestCase
{
    use BackActionFunctionalDataProviderTrait;
    use ClickActionFunctionalDataProviderTrait;
    use ForwardActionFunctionalDataProviderTrait;
    use ReloadActionFunctionalDataProviderTrait;
    use SetActionFunctionalDataProviderTrait;
    use SubmitActionFunctionalDataProviderTrait;
    use WaitActionFunctionalDataProviderTrait;
    use WaitForActionFunctionalDataProviderTrait;

    private ActionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ActionHandler::createHandler();
    }

    /**
     * @dataProvider backActionFunctionalDataProvider
     * @dataProvider clickActionFunctionalDataProvider
     * @dataProvider forwardActionFunctionalDataProvider
     * @dataProvider reloadActionFunctionalDataProvider
     * @dataProvider setActionFunctionalDataProvider
     * @dataProvider submitActionFunctionalDataProvider
     * @dataProvider waitActionFunctionalDataProvider
     * @dataProvider waitForActionFunctionalDataProvider
     *
     * @param array<string, string> $additionalVariableIdentifiers
     */
    public function testHandleForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?BodyInterface $additionalSetupStatements = null,
        ?BodyInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ): void {
        $source = $this->handler->handle($action);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            $additionalSetupStatements,
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
}
