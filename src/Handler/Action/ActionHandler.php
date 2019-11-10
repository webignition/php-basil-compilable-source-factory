<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\BlockInterface;

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

    public static function createHandler(): HandlerInterface
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
        if ($this->browserOperationActionHandler->handles($model)) {
            return true;
        }

        if ($this->clickActionHandler->handles($model)) {
            return true;
        }

        if ($this->setActionHandler->handles($model)) {
            return true;
        }

        if ($this->submitActionHandler->handles($model)) {
            return true;
        }

        if ($this->waitActionHandler->handles($model)) {
            return true;
        }

        if ($this->waitForActionHandler->handles($model)) {
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
     */
    public function handle(object $model): BlockInterface
    {
        if ($this->browserOperationActionHandler->handles($model)) {
            return $this->browserOperationActionHandler->handle($model);
        }

        if ($this->clickActionHandler->handles($model)) {
            return $this->clickActionHandler->handle($model);
        }

        if ($this->setActionHandler->handles($model)) {
            return $this->setActionHandler->handle($model);
        }

        if ($this->submitActionHandler->handles($model)) {
            return $this->submitActionHandler->handle($model);
        }

        if ($this->waitActionHandler->handles($model)) {
            return $this->waitActionHandler->handle($model);
        }

        if ($this->waitForActionHandler->handles($model)) {
            return $this->waitForActionHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }
}
