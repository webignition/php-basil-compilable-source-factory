<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilationSource\SourceInterface;

interface HandlerInterface
{
    public static function createHandler(): HandlerInterface;

    public function handles(object $model): bool;

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createStatementList(object $model): SourceInterface;
}
