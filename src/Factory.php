<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Transpiler\Action\ActionTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Transpiler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\SourceInterface;

class Factory extends AbstractDelegator implements DelegatorInterface, FactoryInterface
{
    public static function createFactory(): FactoryInterface
    {
        return new Factory([
            ScalarValueHandler::createHandler(),
            AssertionHandler::createHandler(),
            ActionTranspiler::createHandler(),
        ]);
    }

    public function isAllowedHandler(HandlerInterface $handler): bool
    {
        return $handler instanceof HandlerInterface;
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

        if ($handler instanceof SourceProducerInterface) {
            return $handler->createSource($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
