<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\SourceInterface;

interface FactoryInterface
{
    public static function createFactory(): FactoryInterface;

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function createSource(object $model): SourceInterface;
}
