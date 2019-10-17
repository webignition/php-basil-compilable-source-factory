<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

interface DelegatorInterface
{
    public function findHandler(object $model): ?HandlerInterface;
    public function isAllowedHandler(HandlerInterface $handler): bool;
}
