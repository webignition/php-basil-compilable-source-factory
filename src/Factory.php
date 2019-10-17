<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilationSource\SourceInterface;

class Factory extends AbstractDelegator implements DelegatorInterface, FactoryInterface
{
    public static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    public function isAllowedHandler(HandlerInterface $handler): bool
    {
        return $handler instanceof TranspilerInterface;
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(object $model): SourceInterface
    {
        $handler = $this->findHandler($model);

        if ($handler instanceof TranspilerInterface) {
            return $handler->transpile($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
