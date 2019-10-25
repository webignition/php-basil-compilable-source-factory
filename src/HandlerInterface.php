<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

interface HandlerInterface extends SourceProducerInterface
{
    public static function createHandler(): HandlerInterface;

    public function handles(object $model): bool;
}
