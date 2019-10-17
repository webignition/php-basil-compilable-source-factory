<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilationSource\SourceInterface;

interface TranspilerInterface
{
    public static function createTranspiler();
    public function handles(object $model): bool;

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface;
}
