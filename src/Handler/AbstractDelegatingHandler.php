<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\AbstractDelegator;
use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\SourceInterface;

abstract class AbstractDelegatingHandler extends AbstractDelegator implements DelegatorInterface, HandlerInterface
{
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
    public function createStatementList(object $model): SourceInterface
    {
        $handler = $this->findHandler($model);

        if ($handler instanceof HandlerInterface) {
            return $handler->createStatementList($model);
        }

        throw new NonTranspilableModelException($model);
    }
}
