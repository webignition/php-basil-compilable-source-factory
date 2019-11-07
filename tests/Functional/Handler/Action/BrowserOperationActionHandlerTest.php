<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\BrowserOperationActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModel\Action\ActionInterface;

class BrowserOperationActionHandlerTest extends AbstractHandlerTest
{
    use BackActionFunctionalDataProviderTrait;
    use ForwardActionFunctionalDataProviderTrait;
    use ReloadActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return BrowserOperationActionHandler::createHandler();
    }

    /**
     * @dataProvider backActionFunctionalDataProvider
     * @dataProvider forwardActionFunctionalDataProvider
     * @dataProvider reloadActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->createSource($action);

        $classCode = $this->testCodeGenerator->createBrowserTestForLineList(
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
