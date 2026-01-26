<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectMethodInvocationTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(
        ExpressionInterface $object,
        string $methodName,
        MethodArgumentsInterface $arguments,
        MetadataInterface $expectedMetadata
    ): void {
        $invocation = new ObjectMethodInvocation(
            object: $object,
            methodName: $methodName,
            arguments: $arguments,
            mightThrow: false
        );

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
                'object' => new VariableDependency(VariableName::PANTHER_CLIENT->value),
                'methodName' => 'method',
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT->value,
                    ],
                ),
            ],
            'has arguments' => [
                'object' => new VariableDependency(VariableName::PANTHER_CLIENT->value),
                'methodName' => 'method',
                'arguments' => new MethodArguments([
                    new LiteralExpression('1'),
                ]),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT->value,
                    ],
                ),
            ],
            'argument expressions contain additional metadata' => [
                'object' => new VariableDependency(VariableName::PANTHER_CLIENT->value),
                'methodName' => 'method',
                'arguments' => new MethodArguments([
                    new ObjectMethodInvocation(
                        object: new StaticObject(ClassName::class),
                        methodName: 'staticMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    )
                ]),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClassName::class,
                    ],
                    variableNames: [
                        VariableName::PANTHER_CLIENT->value,
                    ],
                ),
            ],
            'no arguments, resolving placeholder' => [
                'object' => new Property('object'),
                'methodName' => 'method',
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
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
                    object: new VariableDependency(VariableName::PANTHER_CLIENT->value),
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => '{{ CLIENT }}->methodName()',
            ],
            'static object and method name only' => [
                'invocation' => new ObjectMethodInvocation(
                    object: new StaticObject('parent'),
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => 'parent::methodName()',
            ],
            'object and method name only, error-suppressed' => [
                'invocation' => new ObjectMethodInvocation(
                    object: new VariableDependency(VariableName::PANTHER_CLIENT->value),
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                )->setIsErrorSuppressed(true),
                'expectedString' => '@{{ CLIENT }}->methodName()',
            ],
            'has arguments, inline' => [
                'invocation' => new ObjectMethodInvocation(
                    object: new VariableDependency(VariableName::PANTHER_CLIENT->value),
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ]),
                    mightThrow: false,
                ),
                'expectedString' => "{{ CLIENT }}->methodName(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'invocation' => new ObjectMethodInvocation(
                    object: new VariableDependency(VariableName::PANTHER_CLIENT->value),
                    methodName: 'methodName',
                    arguments: new MethodArguments(
                        [
                            new LiteralExpression('1'),
                            new LiteralExpression("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED
                    ),
                    mightThrow: false,
                ),
                'expectedString' => "{{ CLIENT }}->methodName(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\',\n"
                    . ')',
            ],
            'object and method name only, resolving placeholder' => [
                'invocation' => new ObjectMethodInvocation(
                    object: new Property('object'),
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => '$object->methodName()',
            ],
            'object returned from method call' => [
                'invocation' => new ObjectMethodInvocation(
                    new MethodInvocation(
                        methodName: 'literalMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    ),
                    methodName: 'objectMethodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => 'literalMethodName()->objectMethodName()',
            ],
            'object returned from object method call' => [
                'invocation' => new ObjectMethodInvocation(
                    object: new ObjectMethodInvocation(
                        object: new VariableDependency(VariableName::PANTHER_CLIENT->value),
                        methodName: 'innerMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    ),
                    methodName: 'outerMethodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => '{{ CLIENT }}->innerMethodName()->outerMethodName()',
            ],
        ];
    }
}
