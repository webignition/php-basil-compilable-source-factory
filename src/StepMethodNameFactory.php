<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

class StepMethodNameFactory
{
    public function createTestMethodName(string $stepName): string
    {
        return sprintf('test%s', $this->createMethodIdentifier($stepName));
    }

    public function createDataProviderMethodName(string $stepName): string
    {
        return $this->createMethodIdentifier($stepName) . 'DataProvider';
    }

    private function createMethodIdentifier(string $stepName): string
    {
        return ucfirst(md5($stepName));
    }
}
