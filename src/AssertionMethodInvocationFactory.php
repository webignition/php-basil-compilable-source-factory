<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariableDependency;

class AssertionMethodInvocationFactory
{
    public static function createFactory(): AssertionMethodInvocationFactory
    {
        return new AssertionMethodInvocationFactory();
    }

    /**
     * @param string $assertionMethod
     * @param array<ExpressionInterface> $arguments
     *
     * @return ObjectMethodInvocation
     */
    public function create(string $assertionMethod, array $arguments = []): ObjectMethodInvocation
    {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments,
            MethodInvocation::ARGUMENT_FORMAT_STACKED
        );
    }
}
