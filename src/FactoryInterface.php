<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

interface FactoryInterface extends SourceProducerInterface
{
    public static function createFactory(): FactoryInterface;
}
