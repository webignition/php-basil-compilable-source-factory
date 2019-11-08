<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

interface HandlerInterface
{
    public static function createHandler(): HandlerInterface;
    public function handles(object $model): bool;
    public function handle(object $model);
}
