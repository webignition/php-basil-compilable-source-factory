<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\StatementHandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilModels\Model\Action\ActionInterface;

class ActionHandler
{
    /**
     * @param StatementHandlerInterface[] $handlers
     */
    public function __construct(
        private array $handlers,
    ) {}

    public static function createHandler(): ActionHandler
    {
        return new ActionHandler([
            BrowserOperationActionHandler::createHandler(),
            InteractionActionHandler::createHandler(),
            SetActionHandler::createHandler(),
            WaitActionHandler::createHandler(),
            WaitForActionHandler::createHandler(),
        ]);
    }

    /**
     * @throws UnsupportedStatementException
     */
    public function handle(ActionInterface $action): BodyInterface
    {
        $components = null;

        foreach ($this->handlers as $handler) {
            if (null === $components) {
                try {
                    $components = $handler->handle($action);
                } catch (UnsupportedContentException $unsupportedContentException) {
                    throw new UnsupportedStatementException($action, $unsupportedContentException);
                }
            }
        }

        if (null === $components) {
            throw new UnsupportedStatementException($action);
        }

        $bodyComponents = [];
        $setup = $components->getSetup();
        if ($setup instanceof BodyInterface) {
            $bodyComponents[] = $setup;
            $bodyComponents[] = new EmptyLine();
        }

        $bodyComponents[] = $components->getBody();

        return new Body($bodyComponents);
    }
}
