<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class MethodInvocationTest extends AbstractResolvableTestCase
{
    public function testCreateWithNoArguments(): void
    {
        $methodName = 'methodName';

        $invocation = new MethodInvocation($methodName);
        self::assertSame($methodName, $invocation->getCall());
        self::assertEquals(new Metadata(), $invocation->getMetadata());
    }

    public function testCreateWithArgumentsWithMetadata(): void
    {
        $methodName = 'methodName';
        $arguments = new MethodArguments([
            new ObjectMethodInvocation(
                new StaticObject(ClassName::class),
                'staticMethodName'
            )
        ]);

        $invocation = new MethodInvocation($methodName, $arguments);
        self::assertSame($methodName, $invocation->getCall());
        self::assertSame($arguments, $invocation->getArguments());
        self::assertEquals($arguments->getMetadata(), $invocation->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(MethodInvocationInterface $invocation, string $expectedString): void
    {
        self::assertRenderResolvable($expectedString, $invocation);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'no arguments' => [
                'invocation' => new MethodInvocation('methodName'),
                'expectedString' => 'methodName()',
            ],
            'no arguments, error-suppressed' => [
                'invocation' => new MethodInvocation('methodName')->setIsErrorSuppressed(true),
                'expectedString' => '@methodName()',
            ],
            'has arguments, inline' => [
                'invocation' => new MethodInvocation(
                    'methodName',
                    new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ])
                ),
                'expectedString' => "methodName(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'invocation' => new MethodInvocation(
                    'methodName',
                    new MethodArguments(
                        [
                            new LiteralExpression('1'),
                            new LiteralExpression("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED
                    )
                ),
                'expectedString' => "methodName(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\',\n"
                    . ')',
            ],
        ];
    }
}
