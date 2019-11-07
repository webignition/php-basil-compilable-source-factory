<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitActionHandler;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Action\ActionInterface;

class WaitActionHandlerTest extends AbstractHandlerTest
{
    use WaitActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return WaitActionHandler::createHandler();
    }

    /**
     * @dataProvider waitActionFunctionalDataProvider
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = [],
        ?int $expectedDuration = null
    ) {
        $source = $this->handler->createSource($action);

        $instrumentedSource = clone $source;

        if ($instrumentedSource instanceof LineList) {
            $lines = $source->getLines();
            $lastLine = array_pop($lines);

            $expectedDurationThreshold = $expectedDuration + 1;

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
                    new Statement(
                        '$this->assertGreaterThan(' . $expectedDuration . ', $executionDurationInMilliseconds)'
                    ),
                    new Statement(
                        '$this->assertLessThan(' . $expectedDurationThreshold . ', $executionDurationInMilliseconds)'
                    ),
                ]
            ));
        }

        $classCode = $this->testCodeGenerator->createBrowserTestForLineList(
            $instrumentedSource,
            $fixture,
            $additionalSetupStatements,
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
}
