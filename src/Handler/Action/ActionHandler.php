<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Action\NoArgumentsAction;
use webignition\BasilModel\Action\WaitActionInterface;

class ActionHandler
{
    private $browserOperationActionHandler;
    private $setActionHandler;
    private $waitActionHandler;
    private $waitForActionHandler;
    private $interactionActionHandler;

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
     * @param ActionInterface $action
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(ActionInterface $action): CodeBlockInterface
    {
        if ($this->isBrowserOperationAction($action) && $action instanceof NoArgumentsAction) {
            return $this->browserOperationActionHandler->handle($action);
        }

        if ($this->isInteractionAction($action) && $action instanceof InteractionActionInterface) {
            return $this->interactionActionHandler->handle($action);
        }

        if ($this->isSetAction($action) && $action instanceof InputActionInterface) {
            return $this->setActionHandler->handle($action);
        }

        if ($this->isWaitAction($action) && $action instanceof WaitActionInterface) {
            return $this->waitActionHandler->handle($action);
        }

        if ($this->isWaitForAction($action) && $action instanceof InteractionActionInterface) {
            return $this->waitForActionHandler->handle($action);
        }

        throw new UnsupportedModelException($action);
    }

    private function isBrowserOperationAction(object $model): bool
    {
        return $model instanceof NoArgumentsAction && in_array($model->getType(), [
            ActionTypes::BACK,
            ActionTypes::FORWARD,
            ActionTypes::RELOAD,
        ]);
    }

    private function isInteractionAction(object $model): bool
    {
        return $model instanceof InteractionActionInterface && in_array($model->getType(), [
            ActionTypes::CLICK,
            ActionTypes::SUBMIT,
        ]);
    }

    private function isSetAction(object $model): bool
    {
        return $model instanceof InputActionInterface;
    }

    private function isWaitAction(object $model): bool
    {
        return $model instanceof WaitActionInterface;
    }

    private function isWaitForAction(object $model): bool
    {
        return $model instanceof InteractionActionInterface && ActionTypes::WAIT_FOR === $model->getType();
    }
}
