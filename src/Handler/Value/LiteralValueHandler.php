<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
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
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): BlockInterface
    {
        if ($this->handles($model)) {
            return new Block([
                new Statement((string) $model)
            ]);
        }

        throw new UnsupportedModelException($model);
    }
}
