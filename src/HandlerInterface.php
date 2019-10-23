<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilationSource\SourceInterface;

interface HandlerInterface
{
    // TranspileInterface methods
    public static function createFactory();

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(object $model): SourceInterface;

    // HandlerInterface methods
    public function handles(object $model): bool;
}
