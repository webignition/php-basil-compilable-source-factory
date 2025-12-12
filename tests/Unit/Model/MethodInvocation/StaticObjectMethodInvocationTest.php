<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class StaticObjectMethodInvocationTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        StaticObject $staticObject,
        string $methodName,
        MethodArgumentsInterface $arguments,
        MetadataInterface $expectedMetadata
    ): void {
        $invocation = new StaticObjectMethodInvocation($staticObject, $methodName, $arguments);

        $this->assertSame($staticObject, $invocation->getStaticObject());
        $this->assertSame($methodName, $invocation->getCall());
        $this->assertSame($arguments, $invocation->getArguments());
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'no arguments, string reference' => [
                'staticObject' => new StaticObject(
                    'parent'
                ),
                'methodName' => 'method',
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata(),
            ],
            'no arguments, object reference' => [
                'staticObject' => new StaticObject(
                    ClassName::class
                ),
                'methodName' => 'method',
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClassName::class,
                    ],
                ),
            ],
            'argument expressions contain additional metadata' => [
                'staticObject' => new StaticObject(
                    ClassName::class
                ),
                'methodName' => 'method',
                'arguments' => new MethodArguments([
                    new StaticObjectMethodInvocation(
                        new StaticObject(StaticObject::class),
                        'staticMethodName'
                    )
                ]),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        StaticObject::class,
                        ClassName::class,
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(StaticObjectMethodInvocationInterface $invocation, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $invocation);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'object and method name only, string reference' => [
                'invocation' => new StaticObjectMethodInvocation(
                    new StaticObject(
                        'parent'
                    ),
                    'methodName'
                ),
                'expectedString' => 'parent::methodName()',
            ],
            'object and method name only, object reference' => [
                'invocation' => new StaticObjectMethodInvocation(
                    new StaticObject(
                        ClassName::class
                    ),
                    'methodName'
                ),
                'expectedString' => 'ClassName::methodName()',
            ],
            'object and method name only, object reference, class in root namespace' => [
                'invocation' => new StaticObjectMethodInvocation(
                    new StaticObject(
                        \Throwable::class
                    ),
                    'methodName'
                ),
                'expectedString' => '\Throwable::methodName()',
            ],
            'has arguments, inline' => [
                'invocation' => new StaticObjectMethodInvocation(
                    new StaticObject(
                        ClassName::class
                    ),
                    'methodName',
                    new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ])
                ),
                'expectedString' => "ClassName::methodName(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'invocation' => new StaticObjectMethodInvocation(
                    new StaticObject(
                        ClassName::class
                    ),
                    'methodName',
                    new MethodArguments(
                        [
                            new LiteralExpression('1'),
                            new LiteralExpression("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED
                    )
                ),
                'expectedString' => "ClassName::methodName(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\'\n"
                    . ')',
            ],
        ];
    }
}
