<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class DataParameterHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new DataParameterHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && $model->getType() === ObjectValueType::DATA_PARAMETER;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): SourceInterface
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            return new Statement('$' . $model->getProperty());
        }

        throw new UnsupportedModelException($model);
    }
}
