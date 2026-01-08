<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Json\AssertionMessage;
use webignition\BasilCompilableSourceFactory\Model\Json\FailureMessage;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
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

    public function createAssertionCall(
        string $methodName,
        MethodArgumentsInterface $arguments,
        AssertionMessage $assertionMessage,
    ): MethodInvocationInterface {
        $arguments = $arguments->withArgument($assertionMessage);

        return $this->createCall($methodName, $arguments);
    }

    public function createFailCall(FailureMessage $failureMessage): MethodInvocationInterface
    {
        $arguments = new MethodArguments()->withArgument($failureMessage);

        return $this->createCall('fail', $arguments);
    }
}
