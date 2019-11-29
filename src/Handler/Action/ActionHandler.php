<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InputActionInterface;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\BasilModels\Action\WaitActionInterface;

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
     * @throws UnsupportedActionException
     */
    public function handle(ActionInterface $action): CodeBlockInterface
    {
        try {
            if (in_array($action->getType(), ['back', 'forward', 'reload'])) {
                return $this->browserOperationActionHandler->handle($action);
            }

            if ($action instanceof InteractionActionInterface && in_array($action->getType(), ['click', 'submit'])) {
                return $this->interactionActionHandler->handle($action);
            }

            if ($action instanceof InputActionInterface) {
                return $this->setActionHandler->handle($action);
            }

            if ($action instanceof WaitActionInterface) {
                return $this->waitActionHandler->handle($action);
            }

            if ($action instanceof InteractionActionInterface && in_array($action->getType(), ['wait-for'])) {
                return $this->waitForActionHandler->handle($action);
            }
        } catch (UnsupportedIdentifierException | UnsupportedValueException $previous) {
            throw new UnsupportedActionException($action, $previous);
        }

        throw new UnsupportedActionException($action);
    }
}
