<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
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
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            new MethodArguments($arguments->getArguments(), MethodArguments::FORMAT_STACKED)
        );
    }
}
