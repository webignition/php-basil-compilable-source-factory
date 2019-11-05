<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitForActionHandler;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Action\ActionInterface;

class WaitForActionHandlerTest extends AbstractHandlerTest
{
    use WaitForActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return WaitForActionHandler::createHandler();
    }

    /**
     * @dataProvider waitForActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(string $fixture, ActionInterface $action)
    {
        $source = $this->handler->createSource($action);

        $instrumentedSource = clone $source;

        if ($instrumentedSource instanceof LineList) {
            $lines = $source->getLines();
            $lastLine = array_pop($lines);

            $instrumentedSource = new LineList(array_merge(
                $lines,
                [
                    new EmptyLine(),
                    new Comment('Test harness instrumentation'),
                    new Statement('$before = microtime(true)'),
                    new EmptyLine(),
                    new Comment('Code under test'),
                    $lastLine,
                    new EmptyLine(),
                    new Comment('Test harness instrumentation'),
                    new Statement('$executionDurationInMilliseconds = (microtime(true) - $before) * 1000'),
                    new Statement('$this->assertGreaterThan(100, $executionDurationInMilliseconds)'),
                ]
            ));
        }

        $classCode = $this->testCodeGenerator->createForLineList(
            $instrumentedSource,
            $fixture
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);
        $this->testRunner->run($testRunJob);

        $this->assertSame(
            $testRunJob->getExpectedExitCode(),
            $testRunJob->getExitCode(),
            $testRunJob->getOutputAsString()
        );
    }
}
