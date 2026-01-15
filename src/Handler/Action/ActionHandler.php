<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilModels\Model\Action\ActionInterface;

class ActionHandler
{
    public function __construct(
        private BrowserOperationActionHandler $browserOperationActionHandler,
        private InteractionActionHandler $interactionActionHandler,
        private SetActionHandler $setActionHandler,
        private WaitActionHandler $waitActionHandler,
        private WaitForActionHandler $waitForActionHandler,
    ) {}

    public static function createHandler(): ActionHandler
    {
        return new ActionHandler(
            BrowserOperationActionHandler::createHandler(),
            InteractionActionHandler::createHandler(),
            SetActionHandler::createHandler(),
            WaitActionHandler::createHandler(),
            WaitForActionHandler::createHandler(),
        );
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $components = [];

        try {
            if (in_array($action->getType(), ['back', 'forward', 'reload'])) {
                $components = $this->browserOperationActionHandler->handle($action);
            }

            if ($action->isInteraction()) {
                if (in_array($action->getType(), ['click', 'submit'])) {
                    $components = $this->interactionActionHandler->handle($action);
                }

                if (in_array($action->getType(), ['wait-for'])) {
                    $components = $this->waitForActionHandler->handle($action);
                }
            }

            if ($action->isInput()) {
                $components = $this->setActionHandler->handle($action);
            }

            if ($action->isWait()) {
                $components = $this->waitActionHandler->handle($action);
            }
        } catch (UnsupportedContentException $unsupportedContentException) {
            throw new UnsupportedStatementException($action, $unsupportedContentException);
        }

        if ([] === $components) {
            throw new UnsupportedStatementException($action);
        }

        $bodyComponents = [];
        if ($components['setup'] instanceof BodyInterface) {
            $bodyComponents[] = $components['setup'];
            $bodyComponents[] = new EmptyLine();
        }

        $bodyComponents[] = $components['body'];

        return new Body($bodyComponents);
    }
}
