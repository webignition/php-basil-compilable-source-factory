<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\StepMethodNameFactory;

class StepMethodNameFactoryFactory
{
    /**
     * @param array<string, array<string>> $testMethodNames
     * @param array<string, array<string>> $dataProviderMethodNames
     *
     * @return StepMethodNameFactory
     */
    public function create(
        array $testMethodNames,
        array $dataProviderMethodNames
    ): StepMethodNameFactory {
        $stepMethodNameFactory = \Mockery::mock(StepMethodNameFactory::class);

        foreach ($testMethodNames as $stepName => $stepTestMethodNames) {
            foreach ($stepTestMethodNames as $testMethodName) {
                $stepMethodNameFactory
                    ->shouldReceive('createTestMethodName')
                    ->with($stepName)
                    ->andReturn($testMethodName);
            }
        }

        foreach ($dataProviderMethodNames as $stepName => $stepDataProviderMethodNames) {
            foreach ($stepDataProviderMethodNames as $dataProviderMethodName) {
                $stepMethodNameFactory
                    ->shouldReceive('createDataProviderMethodName')
                    ->with($stepName)
                    ->andReturn($dataProviderMethodName);
            }
        }

        return $stepMethodNameFactory;
    }
}
