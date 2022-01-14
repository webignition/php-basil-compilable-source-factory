<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSource\MethodDefinitionInterface;

class StepMethods
{
    public function __construct(
        private MethodDefinitionInterface $testMethod,
        private ?MethodDefinitionInterface $dataProviderMethod
    ) {
    }

    public function getTestMethod(): MethodDefinitionInterface
    {
        return $this->testMethod;
    }

    public function getDataProviderMethod(): ?MethodDefinitionInterface
    {
        return $this->dataProviderMethod;
    }
}
