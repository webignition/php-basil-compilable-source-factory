<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\FooMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class FooMethodInvocationTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(FooMethodInvocation $methodInvocation, Metadata $expected): void
    {
        self::assertEquals($expected, $methodInvocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'no parent, no arguments' => [
                'methodInvocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expected' => new Metadata(),
            ],
            'no parent, has arguments, no arguments have metadata' => [
                'methodInvocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        new LiteralExpression('"literal string"')
                    ]),
                    mightThrow: false,
                ),
                'expected' => new Metadata(),
            ],
            'no parent, has arguments, arguments have metadata' => [
                'methodInvocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        Property::asEnum(
                            new ClassName(DependencyName::class),
                            DependencyName::ENVIRONMENT_VARIABLE_ARRAY->name,
                        ),
                        Property::asDependency(DependencyName::MESSAGE_FACTORY),
                    ]),
                    mightThrow: false,
                ),
                'expected' => new Metadata(
                    classNames: [
                        DependencyName::class,
                    ],
                    dependencyNames: [
                        DependencyName::MESSAGE_FACTORY,
                    ]
                ),
            ],
            'has parent without metadata, no arguments' => [
                'methodInvocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    parent: Property::asVariable('parent')
                ),
                'expected' => new Metadata(),
            ],
            'has parent with metadata, no arguments' => [
                'methodInvocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    parent: new FooMethodInvocation(
                        methodName: 'parentMethodName',
                        arguments: new MethodArguments([
                            Property::asEnum(
                                new ClassName(DependencyName::class),
                                DependencyName::ENVIRONMENT_VARIABLE_ARRAY->name,
                            ),
                            Property::asDependency(DependencyName::MESSAGE_FACTORY),
                        ]),
                        mightThrow: true,
                    ),
                ),
                'expected' => new Metadata(
                    classNames: [
                        DependencyName::class,
                    ],
                    dependencyNames: [
                        DependencyName::MESSAGE_FACTORY,
                    ]
                ),
            ],
            'has parent with metadata, has arguments with different metadata' => [
                'methodInvocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        Property::asClassConstant(
                            new ClassName(VariableName::class),
                            'EXPECTED_VALUE'
                        ),
                        Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
                    ]),
                    mightThrow: false,
                    parent: new FooMethodInvocation(
                        methodName: 'parentMethodName',
                        arguments: new MethodArguments([
                            Property::asEnum(
                                new ClassName(DependencyName::class),
                                DependencyName::ENVIRONMENT_VARIABLE_ARRAY->name,
                            ),
                            Property::asDependency(DependencyName::MESSAGE_FACTORY),
                        ]),
                        mightThrow: true,
                    ),
                ),
                'expected' => new Metadata(
                    classNames: [
                        VariableName::class,
                        DependencyName::class,
                    ],
                    dependencyNames: [
                        DependencyName::MESSAGE_FACTORY,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(FooMethodInvocation $invocation, string $expectedString): void
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
                'invocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => 'methodName()',
            ],
            'no arguments, error-suppressed' => [
                'invocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                )->setIsErrorSuppressed(),
                'expectedString' => '@methodName()',
            ],
            'has arguments, inline' => [
                'invocation' => new FooMethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ]),
                    mightThrow: false,
                ),
                'expectedString' => "methodName(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'invocation' => new FooMethodInvocation(
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
                'expectedString' => "methodName(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\',\n"
                    . ')',
            ],
        ];
    }
}
