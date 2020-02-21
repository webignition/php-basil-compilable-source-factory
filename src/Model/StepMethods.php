<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSource\MethodDefinitionInterface;

class StepMethods
{
    private $testMethod;
    private $dataProviderMethod;

    public function __construct(MethodDefinitionInterface $testMethod, ?MethodDefinitionInterface $dataProviderMethod)
    {
        $this->testMethod = $testMethod;
        $this->dataProviderMethod = $dataProviderMethod;
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
