<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectMethodInvocationTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        ExpressionInterface $object,
        string $methodName,
        ?MethodArgumentsInterface $arguments,
        MetadataInterface $expectedMetadata
    ): void {
        $invocation = new ObjectMethodInvocation($object, $methodName, $arguments);

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
            'no arguments' => [
                'object' => new VariableDependency('OBJECT'),
                'methodName' => 'method',
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'OBJECT',
                    ]),
                ]),
            ],
            'has arguments' => [
                'object' => new VariableDependency('OBJECT'),
                'methodName' => 'method',
                'arguments' => new MethodArguments([
                    new LiteralExpression('1'),
                ]),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'OBJECT',
                    ]),
                ]),
            ],
            'argument expressions contain additional metadata' => [
                'object' => new VariableDependency('OBJECT'),
                'methodName' => 'method',
                'arguments' => new MethodArguments([
                    new StaticObjectMethodInvocation(
                        new StaticObject(ClassName::class),
                        'staticMethodName'
                    )
                ]),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ClassName::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'OBJECT',
                    ]),
                ]),
            ],
            'no arguments, resolving placeholder' => [
                'object' => new VariableName('object'),
                'methodName' => 'method',
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ObjectMethodInvocation $invocation, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $invocation);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'object and method name only' => [
                'invocation' => new ObjectMethodInvocation(
                    new VariableDependency('OBJECT'),
                    'methodName'
                ),
                'expectedString' => '{{ OBJECT }}->methodName()',
            ],
            'has arguments, inline' => [
                'invocation' => new ObjectMethodInvocation(
                    new VariableDependency('OBJECT'),
                    'methodName',
                    new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ])
                ),
                'expectedString' => "{{ OBJECT }}->methodName(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'invocation' => new ObjectMethodInvocation(
                    new VariableDependency('OBJECT'),
                    'methodName',
                    new MethodArguments(
                        [
                            new LiteralExpression('1'),
                            new LiteralExpression("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED
                    )
                ),
                'expectedString' => "{{ OBJECT }}->methodName(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\'\n"
                    . ')',
            ],
            'object and method name only, resolving placeholder' => [
                'invocation' => new ObjectMethodInvocation(
                    new VariableName('object'),
                    'methodName'
                ),
                'expectedString' => '$object->methodName()',
            ],
            'object returned from method call' => [
                'invocation' => new ObjectMethodInvocation(
                    new MethodInvocation(
                        'literalMethodName'
                    ),
                    'objectMethodName'
                ),
                'expectedString' => 'literalMethodName()->objectMethodName()',
            ],
            'object returned from object method call' => [
                'invocation' => new ObjectMethodInvocation(
                    new ObjectMethodInvocation(
                        new VariableDependency('OBJECT'),
                        'innerMethodName'
                    ),
                    'outerMethodName'
                ),
                'expectedString' => '{{ OBJECT }}->innerMethodName()->outerMethodName()',
            ],
        ];
    }
}
