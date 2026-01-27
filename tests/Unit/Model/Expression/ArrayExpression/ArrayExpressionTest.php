<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression\ArrayExpression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayPair;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ArrayExpressionTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(ArrayExpression $expression, MetadataInterface $expectedMetadata): void
    {
        self::assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new ArrayExpression([]),
                'expectedMetadata' => new Metadata(),
            ],
            'no metadata' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key1',
                        new LiteralExpression('\'value1\'')
                    ),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key3',
                        new ObjectMethodInvocation(
                            object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                        )
                    ),
                ]),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        DependencyName::PANTHER_CLIENT->value,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(ArrayExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new ArrayExpression([]),
                'expectedString' => '[]',
            ],
            'single pair' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key1',
                        new LiteralExpression('\'value1\'')
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'key1' => 'value1',\n"
                    . ']',
            ],
            'multiple pairs' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key1',
                        new LiteralExpression('\'value1\'')
                    ),
                    new ArrayPair(
                        'key2',
                        Property::asVariable('variableName')
                    ),
                    new ArrayPair(
                        'key3',
                        new ObjectMethodInvocation(
                            object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                        )
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'key1' => 'value1',\n"
                    . "    'key2' => \$variableName,\n"
                    . "    'key3' => {{ CLIENT }}->methodName(),\n"
                    . ']',
            ],
            'single data set with single key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    '0' => [\n"
                    . "        'key1' => 'value1',\n"
                    . "    ],\n"
                    . ']',
            ],
            'single data set with single key:value string name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'data-set-one' => [\n"
                    . "        'key1' => 'value1',\n"
                    . "    ],\n"
                    . ']',
            ],
            'single data set with multiple key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'')
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    '0' => [\n"
                    . "        'key1' => 'value1',\n"
                    . "        'key2' => 'value2',\n"
                    . "    ],\n"
                    . ']',
            ],
            'multiple data sets with multiple key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'')
                            ),
                        ])
                    ),
                    new ArrayPair(
                        '1',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value3\'')
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value4\'')
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    '0' => [\n"
                    . "        'key1' => 'value1',\n"
                    . "        'key2' => 'value2',\n"
                    . "    ],\n"
                    . "    '1' => [\n"
                    . "        'key1' => 'value3',\n"
                    . "        'key2' => 'value4',\n"
                    . "    ],\n"
                    . ']',
            ],

            'single data set with VariableName value' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                Property::asVariable('variableName')
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'data-set-one' => [\n"
                    . "        'key1' => \$variableName,\n"
                    . "    ],\n"
                    . ']',
            ],
            'single data set with ObjectMethodInvocation value' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new ObjectMethodInvocation(
                                    object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                                    methodName: 'methodName',
                                    arguments: new MethodArguments(),
                                    mightThrow: false,
                                )
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'data-set-one' => [\n"
                    . "        'key1' => {{ CLIENT }}->methodName(),\n"
                    . "    ],\n"
                    . ']',
            ],
        ];
    }

    #[DataProvider('fromArrayDataProvider')]
    public function testFromArray(ArrayExpression $expression, ArrayExpression $expectedExpression): void
    {
        self::assertEquals($expectedExpression, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function fromArrayDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => ArrayExpression::fromArray([]),
                'expectedExpression' => new ArrayExpression([]),
            ],
            'single data set with single key:value numerical name' => [
                'expression' => ArrayExpression::fromArray([
                    0 => [
                        'key1' => 'value1',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with single key:value string name' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => 'value1',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with multiple key:value numerical name' => [
                'expression' => ArrayExpression::fromArray([
                    0 => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'')
                            ),
                        ])
                    ),
                ]),
            ],
            'multiple data sets with multiple key:value numerical name' => [
                'expression' => ArrayExpression::fromArray([
                    0 => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                    1 => [
                        'key1' => 'value3',
                        'key2' => 'value4',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'')
                            ),
                        ])
                    ),
                    new ArrayPair(
                        '1',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value3\'')
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value4\'')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with VariableName value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => Property::asVariable('variableName'),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                Property::asVariable('variableName')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with ObjectMethodInvocation value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => new ObjectMethodInvocation(
                            object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                        ),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new ObjectMethodInvocation(
                                    object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                                    methodName: 'methodName',
                                    arguments: new MethodArguments(),
                                    mightThrow: false,
                                )
                            ),
                        ])
                    ),
                ]),
            ],
            'array of scalars' => [
                'expression' => ArrayExpression::fromArray([
                    'data' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data',
                        ArrayExpression::fromArray([
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ])
                    )
                ]),
            ],
            'array with nested array' => [
                'expression' => ArrayExpression::fromArray([
                    'name' => new ObjectMethodInvocation(
                        object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                        methodName: 'dataName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    ),
                    'data' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'name',
                        new ObjectMethodInvocation(
                            object: new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                            methodName: 'dataName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                        )
                    ),
                    new ArrayPair(
                        'data',
                        ArrayExpression::fromArray([
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ])
                    )
                ]),
            ],
        ];
    }
}
