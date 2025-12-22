<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Renderable\FailureMessage;
use webignition\BasilCompilableSourceFactory\Renderable\Statement;

readonly class PhpUnitCallFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
    ) {}

    public static function createFactory(): self
    {
        return new PhpUnitCallFactory(
            ArgumentFactory::createFactory(),
        );
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
        Statement $assertion
    ): MethodInvocationInterface {
        $arguments = $arguments->withArgument(
            $this->argumentFactory->createSingular((string) json_encode($assertion, JSON_PRETTY_PRINT))
        );

        return $this->createCall($methodName, $arguments);
    }

    public function createFailCall(FailureMessage $failureMessage): MethodInvocationInterface
    {
        $serializedMetadata = (string) json_encode($failureMessage, JSON_PRETTY_PRINT);

        $arguments = new MethodArguments()->withArgument(
            $this->argumentFactory->createSingular($serializedMetadata)
        );

        return $this->createCall('fail', $arguments);
    }
}
