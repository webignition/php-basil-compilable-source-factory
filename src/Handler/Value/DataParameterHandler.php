<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class DataParameterHandler implements HandlerInterface
{
    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && $model->getType() === ObjectValueType::DATA_PARAMETER;
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
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            return new Block([
                new Statement('$' . $model->getProperty())
            ]);
        }

        throw new UnsupportedModelException($model);
    }
}
