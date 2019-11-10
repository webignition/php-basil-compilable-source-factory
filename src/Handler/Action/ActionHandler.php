<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\Block\BlockInterface;
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
     * @param object $model
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(object $model): BlockInterface
    {
        if ($this->isBrowserOperationAction($model)) {
            return $this->browserOperationActionHandler->handle($model);
        }

        if ($this->isInteractionAction($model)) {
            return $this->interactionActionHandler->handle($model);
        }

        if ($this->isSetAction($model)) {
            return $this->setActionHandler->handle($model);
        }

        if ($this->isWaitAction($model)) {
            return $this->waitActionHandler->handle($model);
        }

        if ($this->isWaitForAction($model)) {
            return $this->waitForActionHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
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
