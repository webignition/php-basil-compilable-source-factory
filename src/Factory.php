<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilationSource\StatementListInterface;

class Factory extends AbstractDelegator implements DelegatorInterface, FactoryInterface
{
    public static function createFactory(): FactoryInterface
    {
        return new Factory([
            ScalarValueHandler::createHandler(),
            AssertionHandler::createHandler(),
            ActionHandler::createHandler(),
        ]);
    }

    public function isAllowedHandler(HandlerInterface $handler): bool
    {
        return $handler instanceof HandlerInterface;
    }

    /**
     * @param object $model
     *
     * @return StatementListInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(object $model): StatementListInterface
    {
        $handler = $this->findHandler($model);

        if ($handler instanceof SourceProducerInterface) {
            return $handler->createSource($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
