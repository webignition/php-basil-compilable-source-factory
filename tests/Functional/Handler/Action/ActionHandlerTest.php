<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

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

    /**
     * @var ActionHandler
     */
    private $handler;

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
     */
    public function testCreateSourceForExecutableActions(
        string $fixture,
        ActionInterface $action,
        ?Block $additionalSetupStatements = null,
        ?Block $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($action);

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

    /**
     * @dataProvider createSourceForFailingActionsDataProvider
     */
    public function testCreateSourceForFailingActions(
        string $fixture,
        ActionInterface $action,
        string $expectedExpectationFailedExceptionMessage,
        ?Block $additionalSetupStatements = null,
        ?Block $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($action);

        $classCode = $this->testCodeGenerator->createBrowserTestForLineList(
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

    public function createSourceForFailingActionsDataProvider(): array
    {
        return [
            'wait action, element identifier examined value, element does not exist' => [
                'fixture' => '/action-wait.html',
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('.non-existent'))
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ],
            ],
            'wait, attribute identifier examined value, element does not exist' => [
                'fixture' => '/action-wait.html',
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('.non-existent'))->withAttributeName('attribute_name')
                    )
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
