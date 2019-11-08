<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\AbstractDelegator;
use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
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
     * @throws UnsupportedModelException
     */
    public function handle(object $model): SourceInterface
    {
        $handler = $this->findHandler($model);

        if ($handler instanceof HandlerInterface) {
            return $handler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }
}
