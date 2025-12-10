<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodArguments;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class MethodArgumentsTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param ExpressionInterface[] $arguments
     */
    public function testCreate(
        array $arguments,
        string $format,
        MetadataInterface $expectedMetadata
    ): void {
        $methodArguments = new MethodArguments($arguments, $format);

        $this->assertSame($arguments, $methodArguments->getArguments());
        $this->assertSame($format, $methodArguments->getFormat());
        $this->assertEquals($expectedMetadata, $methodArguments->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty, inline' => [
                'arguments' => [],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(),
            ],
            'empty, stacked' => [
                'arguments' => [],
                'format' => MethodArgumentsInterface::FORMAT_STACKED,
                'expectedMetadata' => new Metadata(),
            ],
            'single argument' => [
                'arguments' => [
                    new LiteralExpression('1'),
                ],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(),
            ],
            'multiple arguments' => [
                'arguments' => [
                    new LiteralExpression('2'),
                    new LiteralExpression("\\'single-quoted value\\'"),
                    new LiteralExpression('"double-quoted value"'),
                ],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'arguments' => [
                    new StaticObjectMethodInvocation(
                        new StaticObject(ClassName::class),
                        'staticMethodName'
                    )
                ],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ClassName::class),
                        ])
                    )
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(MethodArguments $arguments, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $arguments);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty, inline' => [
                'arguments' => new MethodArguments([]),
                'expectedString' => '',
            ],
            'empty, stacked' => [
                'arguments' => new MethodArguments([], MethodArgumentsInterface::FORMAT_STACKED),
                'expectedString' => '',
            ],
            'has arguments, inline' => [
                'arguments' => new MethodArguments([
                    new LiteralExpression('1'),
                    new LiteralExpression("\\'single-quoted value\\'"),
                ]),
                'expectedString' => "1, \\'single-quoted value\\'",
            ],
            'has arguments, stacked' => [
                'arguments' => new MethodArguments(
                    [
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ],
                    MethodArgumentsInterface::FORMAT_STACKED
                ),
                'expectedString' => "\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\'\n",
            ],
            'indent stacked multi-line arguments' => [
                'arguments' => new MethodArguments(
                    [
                        new ObjectMethodInvocation(
                            new VariableDependency('NAVIGATOR'),
                            'find',
                            new MethodArguments([
                                new StaticObjectMethodInvocation(
                                    new StaticObject(ObjectMethodInvocation::class),
                                    'fromJson',
                                    new MethodArguments([
                                        new LiteralExpression(
                                            '{' . "\n" . '    "locator": ".selector"' . "\n" . '}'
                                        ),
                                    ])
                                )
                            ])
                        ),
                        new ClosureExpression(
                            new Body([
                                new Statement(
                                    new AssignmentExpression(
                                        new VariableName('variable'),
                                        new LiteralExpression('100')
                                    )
                                ),
                                new EmptyLine(),
                                new Statement(
                                    new ReturnExpression(
                                        new VariableName('variable'),
                                    )
                                ),
                            ])
                        ),
                    ],
                    MethodArgumentsInterface::FORMAT_STACKED
                ),
                'expectedString' => "\n"
                    . '    {{ NAVIGATOR }}->find(ObjectMethodInvocation::fromJson({' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    })),' . "\n"
                    . '    (function () {' . "\n"
                    . '        $variable = 100;' . "\n"
                    . "\n"
                    . '        return $variable;' . "\n"
                    . '    })()' . "\n",
            ],
            'single array expression, pair contains inner array' => [
                'arguments' => new MethodArguments(
                    [
                        ArrayExpression::fromArray([
                            'name' => new ObjectMethodInvocation(
                                new VariableDependency('DEPENDENCY'),
                                'dataName'
                            ),
                            'data' => [
                                'key1' => 'value1',
                                'key2' => 'value2',
                            ],
                        ])
                    ]
                ),
                'expectedString' => "[\n"
                    . "    'name' => {{ DEPENDENCY }}->dataName(),\n"
                    . "    'data' => [\n"
                    . "        'key1' => 'value1',\n"
                    . "        'key2' => 'value2',\n"
                    . "    ],\n"
                    . ']',
            ],
        ];
    }
}
