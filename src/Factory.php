<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\SourceInterface;

class Factory implements FactoryInterface
{
    public static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function createSource(object $model): SourceInterface
    {
        throw new UnsupportedModelException($model);
    }
}
