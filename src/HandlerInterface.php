<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilationSource\StatementListInterface;

interface HandlerInterface
{
    public static function createHandler(): HandlerInterface;

    public function handles(object $model): bool;

    /**
     * @param object $model
     *
     * @return StatementListInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createStatementList(object $model): StatementListInterface;
}
