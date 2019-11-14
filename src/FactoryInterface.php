<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;

interface FactoryInterface
{
    public static function createFactory(): FactoryInterface;

    /**
     * @param object $model
     *
     * @return ClassDefinitionInterface
     *
     * @throws UnsupportedModelException
     */
    public function createClassDefinition(object $model): ClassDefinitionInterface;
}
