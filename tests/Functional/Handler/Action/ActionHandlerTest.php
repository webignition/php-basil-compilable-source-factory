<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Action;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

class ActionHandlerTest extends AbstractHandlerTest
{
    use WaitActionFunctionalDataProviderTrait;
    use WaitForActionFunctionalDataProviderTrait;
    use BackActionFunctionalDataProviderTrait;
    use ForwardActionFunctionalDataProviderTrait;
    use ReloadActionFunctionalDataProviderTrait;
    use ClickActionFunctionalDataProviderTrait;
    use SubmitActionFunctionalDataProviderTrait;
    use SetActionFunctionalDataProviderTrait;

    protected function createHandler(): HandlerInterface
    {
        return ActionHandler::createHandler();
    }

    /**
     * @dataProvider createSourceForExecutableActionsDataProvider
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

        $this->assertSame(
            $testRunJob->getExpectedExitCode(),
            $testRunJob->getExitCode(),
            $testRunJob->getOutputAsString()
        );
    }

    public function createSourceForExecutableActionsDataProvider()
    {
        return [
            'wait action' => current($this->waitActionFunctionalDataProvider()),
            'wait-for action' => current($this->waitForActionFunctionalDataProvider()),
            'back action' => current($this->backActionFunctionalDataProvider()),
            'forward action' => current($this->forwardActionFunctionalDataProvider()),
            'reload action' => current($this->reloadActionFunctionalDataProvider()),
            'click action' => current($this->clickActionFunctionalDataProvider()),
            'submit action' => current($this->submitActionFunctionalDataProvider()),
            'set action' => current($this->setActionFunctionalDataProvider()),
        ];
    }

    /**
     * @dataProvider createSourceForFailingActionsDataProvider
     */
    public function testCreateSourceForFailingActions(
        string $fixture,
        ActionInterface $action,
        string $expectedExpectationFailedExceptionMessage,
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

        $testRunJob = $this->testRunner->createTestRunJob($classCode, 1);
        $this->testRunner->run($testRunJob);

        $this->assertSame(
            $testRunJob->getExpectedExitCode(),
            $testRunJob->getExitCode(),
            $testRunJob->getOutputAsString()
        );
        $this->assertStringContainsString($expectedExpectationFailedExceptionMessage, $testRunJob->getOutputAsString());
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
                    'HAS' => self::HAS_VARIABLE_NAME,
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
                    'HAS' => self::HAS_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
