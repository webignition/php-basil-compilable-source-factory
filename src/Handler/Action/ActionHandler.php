<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Action\ActionInterface;

class ActionHandler
{
    private BrowserOperationActionHandler $browserOperationActionHandler;
    private SetActionHandler $setActionHandler;
    private WaitActionHandler $waitActionHandler;
    private WaitForActionHandler $waitForActionHandler;
    private InteractionActionHandler $interactionActionHandler;

    public function __construct(
        BrowserOperationActionHandler $browserOperationActionHandler,
        InteractionActionHandler $interactionActionHandler,
        SetActionHandler $setActionHandler,
        WaitActionHandler $waitActionHandler,
        WaitForActionHandler $waitForActionHandler
    ) {
        $this->browserOperationActionHandler = $browserOperationActionHandler;
        $this->interactionActionHandler = $interactionActionHandler;
        $this->setActionHandler = $setActionHandler;
        $this->waitActionHandler = $waitActionHandler;
        $this->waitForActionHandler = $waitForActionHandler;
    }

    public static function createHandler(): ActionHandler
    {
        return new ActionHandler(
            BrowserOperationActionHandler::createHandler(),
            InteractionActionHandler::createHandler(),
            SetActionHandler::createHandler(),
            WaitActionHandler::createHandler(),
            WaitForActionHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        try {
            if (in_array($action->getType(), ['back', 'forward', 'reload'])) {
                return $this->addRefreshCrawlerAndNavigatorStatement(
                    $this->browserOperationActionHandler->handle($action)
                );
            }

            if ($action->isInteraction()) {
                if (in_array($action->getType(), ['click', 'submit'])) {
                    return $this->addRefreshCrawlerAndNavigatorStatement(
                        $this->interactionActionHandler->handle($action)
                    );
                }

                if (in_array($action->getType(), ['wait-for'])) {
                    return $this->waitForActionHandler->handle($action);
                }
            }

            if ($action->isInput()) {
                return $this->addRefreshCrawlerAndNavigatorStatement($this->setActionHandler->handle($action));
            }

            if ($action->isWait()) {
                return $this->waitActionHandler->handle($action);
            }
        } catch (UnsupportedContentException $unsupportedContentException) {
            throw new UnsupportedStatementException($action, $unsupportedContentException);
        }

        throw new UnsupportedStatementException($action);
    }

    private function addRefreshCrawlerAndNavigatorStatement(BodyInterface $body): BodyInterface
    {
        return new Body([
            $body,
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'refreshCrawlerAndNavigator'
                )
            ),
        ]);
    }
}
