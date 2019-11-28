<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilDataStructure\Action\ActionInterface;

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
     * @throws UnknownIdentifierException
     * @throws UnsupportedActionException
     * @throws UnsupportedModelException
     * @throws UnsupportedValueException
     */
    public function handle(ActionInterface $action): CodeBlockInterface
    {
        if ($this->isBrowserOperationAction($action)) {
            return $this->browserOperationActionHandler->handle($action);
        }

        if ($this->isInteractionAction($action)) {
            return $this->interactionActionHandler->handle($action);
        }

        if ('set' === $action->getType()) {
            return $this->setActionHandler->handle($action);
        }

        if ('wait' === $action->getType()) {
            return $this->waitActionHandler->handle($action);
        }

        if ('wait-for' === $action->getType()) {
            return $this->waitForActionHandler->handle($action);
        }

        throw new UnsupportedModelException($action);
    }

    private function isBrowserOperationAction(ActionInterface $action): bool
    {
        return in_array($action->getType(), [
            'back',
            'forward',
            'reload',
        ]);
    }

    private function isInteractionAction(ActionInterface $action): bool
    {
        return in_array($action->getType(), [
            'click',
            'submit',
        ]);
    }
}
