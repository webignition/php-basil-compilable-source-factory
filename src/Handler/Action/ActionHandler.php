<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputActionInterface;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Action\NoArgumentsAction;
use webignition\BasilModel\Action\WaitActionInterface;

class ActionHandler implements HandlerInterface
{
    private $browserOperationActionHandler;
    private $clickActionHandler;
    private $setActionHandler;
    private $submitActionHandler;
    private $waitActionHandler;
    private $waitForActionHandler;

    public function __construct(
        BrowserOperationActionHandler $browserOperationActionHandler,
        ClickActionHandler $clickActionHandler,
        SetActionHandler $setActionHandler,
        SubmitActionHandler $submitActionHandler,
        WaitActionHandler $waitActionHandler,
        WaitForActionHandler $waitForActionHandler
    ) {
        $this->browserOperationActionHandler = $browserOperationActionHandler;
        $this->clickActionHandler = $clickActionHandler;
        $this->setActionHandler = $setActionHandler;
        $this->submitActionHandler = $submitActionHandler;
        $this->waitActionHandler = $waitActionHandler;
        $this->waitForActionHandler = $waitForActionHandler;
    }

    public static function createHandler(): ActionHandler
    {
        return new ActionHandler(
            BrowserOperationActionHandler::createHandler(),
            ClickActionHandler::createHandler(),
            SetActionHandler::createHandler(),
            SubmitActionHandler::createHandler(),
            WaitActionHandler::createHandler(),
            WaitForActionHandler::createHandler()
        );
    }

    public function handles(object $model): bool
    {
        if ($this->isBrowserOperationAction($model)) {
            return true;
        }

        if ($this->clickActionHandler->handles($model)) {
            return true;
        }

        if ($this->isSetAction($model)) {
            return true;
        }

        if ($this->submitActionHandler->handles($model)) {
            return true;
        }

        if ($this->isWaitAction($model)) {
            return true;
        }

        if ($this->isWaitForAction($model)) {
            return true;
        }

        return false;
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

        if ($this->clickActionHandler->handles($model)) {
            return $this->clickActionHandler->handle($model);
        }

        if ($this->isSetAction($model)) {
            return $this->setActionHandler->handle($model);
        }

        if ($this->submitActionHandler->handles($model)) {
            return $this->submitActionHandler->handle($model);
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
