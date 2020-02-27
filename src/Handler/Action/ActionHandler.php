<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\VariableNames;
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
     * @throws UnsupportedStatementException
     */
    public function handle(ActionInterface $action): CodeBlockInterface
    {
        try {
            if (in_array($action->getType(), ['back', 'forward', 'reload'])) {
                return $this->addRefreshCrawlerStatement($this->browserOperationActionHandler->handle($action));
            }

            if ($action instanceof InteractionActionInterface && in_array($action->getType(), ['click', 'submit'])) {
                return $this->addRefreshCrawlerStatement($this->interactionActionHandler->handle($action));
            }

            if ($action instanceof InputActionInterface) {
                return $this->addRefreshCrawlerStatement($this->setActionHandler->handle($action));
            }

            if ($action instanceof WaitActionInterface) {
                return $this->waitActionHandler->handle($action);
            }

            if ($action instanceof InteractionActionInterface && in_array($action->getType(), ['wait-for'])) {
                return $this->waitForActionHandler->handle($action);
            }
        } catch (UnsupportedContentException $unsupportedContentException) {
            throw new UnsupportedStatementException($action, $unsupportedContentException);
        }

        throw new UnsupportedStatementException($action);
    }

    private function addRefreshCrawlerStatement(CodeBlockInterface $codeBlock): CodeBlockInterface
    {
        $codeBlock->addLines([
            new AssignmentStatement(
                VariablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                    'refreshCrawler'
                )
            ),
        ]);

        return $codeBlock;
    }
}
