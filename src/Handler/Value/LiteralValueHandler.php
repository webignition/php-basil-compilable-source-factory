<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Line\StatementInterface;
use webignition\BasilModel\Value\LiteralValueInterface;

class LiteralValueHandler implements HandlerInterface
{
    public function handles(object $model): bool
    {
        return $model instanceof LiteralValueInterface;
    }

    /**
     * @param object $model
     *
     * @return StatementInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): StatementInterface
    {
        if ($this->handles($model)) {
            return new Statement((string) $model);
        }

        throw new UnsupportedModelException($model);
    }
}
