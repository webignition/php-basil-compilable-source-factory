<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\SubmitActionHandler;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModel\Action\ActionInterface;

class SubmitActionHandlerTest extends AbstractHandlerTest
{
    use SubmitActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return SubmitActionHandler::createHandler();
    }

    /**
     * @dataProvider submitActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->createSource($action);

        $classCode = $this->testCodeGenerator->createForLineList(
            $source,
            $fixture,
            $additionalSetupStatements,
            $teardownStatements,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);
        $this->testRunner->run($testRunJob);
        $exitCode = $testRunJob->getExitCode();

        $this->assertSame(0, $exitCode, $testRunJob->getOutputAsString());
    }
}
