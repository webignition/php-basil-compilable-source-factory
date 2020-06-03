<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\ResolvablePlaceholder;

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
            ResolvablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments,
            MethodInvocation::ARGUMENT_FORMAT_STACKED
        );
    }
}
