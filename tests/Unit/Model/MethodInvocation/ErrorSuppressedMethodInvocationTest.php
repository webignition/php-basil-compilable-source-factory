<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ErrorSuppressedMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTest;

class ErrorSuppressedMethodInvocationTest extends AbstractResolvableTest
{
    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ErrorSuppressedMethodInvocation $invocation, string $expectedString): void
    {
        self::assertRenderResolvable($expectedString, $invocation);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'MethodInvocation' => [
                'invocation' => new ErrorSuppressedMethodInvocation(
                    new MethodInvocation('methodName')
                ),
                'expectedString' => '@methodName()',
            ],
            'ObjectMethodInvocation' => [
                'invocation' => new ErrorSuppressedMethodInvocation(
                    new ObjectMethodInvocation(
                        new VariableDependency('OBJECT'),
                        'methodName'
                    )
                ),
                'expectedString' => '@{{ OBJECT }}->methodName()',
            ],
            'StaticObjectMethodInvocation' => [
                'invocation' => new ErrorSuppressedMethodInvocation(
                    new StaticObjectMethodInvocation(
                        new StaticObject(
                            'parent'
                        ),
                        'methodName'
                    )
                ),
                'expectedString' => '@parent::methodName()',
            ],
        ];
    }
}
