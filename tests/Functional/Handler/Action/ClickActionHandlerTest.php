<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\ClickActionHandler;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModel\Action\ActionInterface;

class ClickActionHandlerTest extends AbstractHandlerTest
{
    use ClickActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return ClickActionHandler::createHandler();
    }

    /**
     * @dataProvider clickActionFunctionalDataProvider
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

        echo "\n\n" . $classCode . "\n\n";

        $this->testRunner->run($testRunJob);
        $exitCode = $testRunJob->getExitCode();

        $this->assertSame(0, $exitCode, $testRunJob->getOutputAsString());
    }
}
