<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilModel\Value\LiteralValueInterface;

class LiteralValueHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new LiteralValueHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof LiteralValueInterface;
    }

    public function createStatementList(object $model): StatementListInterface
    {
        if ($this->handles($model)) {
            return new StatementList([
                new Statement((string) $model),
            ]);
        }

        throw new NonTranspilableModelException($model);
    }
}
