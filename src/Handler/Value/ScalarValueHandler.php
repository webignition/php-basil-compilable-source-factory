<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\BlockInterface;

class ScalarValueHandler implements HandlerInterface
{
    private $browserPropertyHandler;
    private $dataParameterHandler;
    private $environmentValueHandler;
    private $literalValueHandler;
    private $pagePropertyHandler;

    public function __construct(
        BrowserPropertyHandler $browserPropertyHandler,
        DataParameterHandler $dataParameterHandler,
        EnvironmentValueHandler $environmentValueHandler,
        LiteralValueHandler $literalValueHandler,
        PagePropertyHandler $pagePropertyHandler
    ) {
        $this->browserPropertyHandler = $browserPropertyHandler;
        $this->dataParameterHandler = $dataParameterHandler;
        $this->environmentValueHandler = $environmentValueHandler;
        $this->literalValueHandler = $literalValueHandler;
        $this->pagePropertyHandler = $pagePropertyHandler;
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new BrowserPropertyHandler(),
            new DataParameterHandler(),
            new EnvironmentValueHandler(),
            new LiteralValueHandler(),
            new PagePropertyHandler()
        );
    }

    public function handles(object $model): bool
    {
        if ($this->browserPropertyHandler->handles($model)) {
            return true;
        }

        if ($this->dataParameterHandler->handles($model)) {
            return true;
        }

        if ($this->environmentValueHandler->handles($model)) {
            return true;
        }

        if ($this->literalValueHandler->handles($model)) {
            return true;
        }

        if ($this->pagePropertyHandler->handles($model)) {
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
        if ($this->browserPropertyHandler->handles($model)) {
            return $this->browserPropertyHandler->handle($model);
        }

        if ($this->dataParameterHandler->handles($model)) {
            return $this->dataParameterHandler->handle($model);
        }

        if ($this->environmentValueHandler->handles($model)) {
            return $this->environmentValueHandler->handle($model);
        }

        if ($this->literalValueHandler->handles($model)) {
            return $this->literalValueHandler->handle($model);
        }

        if ($this->pagePropertyHandler->handles($model)) {
            return $this->pagePropertyHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }
}
