<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class ScalarValueHandler
{
    private $browserPropertyHandler;
    private $environmentValueHandler;
    private $literalValueHandler;
    private $pagePropertyHandler;

    public function __construct(
        BrowserPropertyHandler $browserPropertyHandler,
        EnvironmentValueHandler $environmentValueHandler,
        LiteralValueHandler $literalValueHandler,
        PagePropertyHandler $pagePropertyHandler
    ) {
        $this->browserPropertyHandler = $browserPropertyHandler;
        $this->environmentValueHandler = $environmentValueHandler;
        $this->literalValueHandler = $literalValueHandler;
        $this->pagePropertyHandler = $pagePropertyHandler;
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new BrowserPropertyHandler(),
            new EnvironmentValueHandler(),
            new LiteralValueHandler(),
            new PagePropertyHandler()
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
        if ($model instanceof ObjectValueInterface && ObjectValueType::BROWSER_PROPERTY === $model->getType()) {
            return $this->browserPropertyHandler->handle($model);
        }

        if ($model instanceof ObjectValueInterface && $model->getType() === ObjectValueType::DATA_PARAMETER) {
            return new Block([
                new Statement('$' . $model->getProperty())
            ]);
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
