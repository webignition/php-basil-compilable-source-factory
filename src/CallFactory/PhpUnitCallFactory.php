<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;

readonly class PhpUnitCallFactory
{
    public static function createFactory(): self
    {
        return new PhpUnitCallFactory();
    }

    public function createCall(
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ): MethodInvocationInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
            $methodName,
            $arguments
        );
    }

    public function createFailCall(MethodArgumentsInterface $arguments): MethodInvocationInterface
    {
        return $this->createCall('fail', $arguments);
    }
}
