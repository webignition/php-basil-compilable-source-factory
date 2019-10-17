<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

interface HandlerInterface
{
    public function handles(object $model): bool;
}
