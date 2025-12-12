<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;

class AssertionMethodInvocationFactory
{
    public static function createFactory(): AssertionMethodInvocationFactory
    {
        return new AssertionMethodInvocationFactory();
    }

    public function create(
        string $assertionMethod,
        MethodArgumentsInterface $arguments,
    ): MethodInvocationInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        );
    }
}
