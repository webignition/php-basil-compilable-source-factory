<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression\ArrayExpression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayPair;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
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
                        new LiteralExpression('\'value1\'', Type::STRING)
                    ),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key3',
                        new MethodInvocation(
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: Type::STRING,
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        )
                    ),
                ]),
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
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
                        new LiteralExpression('\'value1\'', Type::STRING)
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
                        new LiteralExpression('\'value1\'', Type::STRING)
                    ),
                    new ArrayPair(
                        'key2',
                        Property::asVariable('variableName', Type::STRING)
                    ),
                    new ArrayPair(
                        'key3',
                        new MethodInvocation(
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: Type::STRING,
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
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
                                new LiteralExpression('\'value1\'', Type::STRING)
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
                                new LiteralExpression('\'value1\'', Type::STRING)
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
                                new LiteralExpression('\'value1\'', Type::STRING)
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'', Type::STRING)
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
                                new LiteralExpression('\'value1\'', Type::STRING)
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'', Type::STRING)
                            ),
                        ])
                    ),
                    new ArrayPair(
                        '1',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value3\'', Type::STRING)
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value4\'', Type::STRING)
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
                                Property::asVariable('variableName', Type::STRING)
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
                                new MethodInvocation(
                                    methodName: 'methodName',
                                    arguments: new MethodArguments(),
                                    mightThrow: false,
                                    type: Type::STRING,
                                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
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
                                new LiteralExpression('\'value1\'', Type::STRING)
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
                                new LiteralExpression('\'value1\'', Type::STRING)
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
                                new LiteralExpression('\'value1\'', Type::STRING)
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'', Type::STRING)
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
                                new LiteralExpression('\'value1\'', Type::STRING)
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value2\'', Type::STRING)
                            ),
                        ])
                    ),
                    new ArrayPair(
                        '1',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new LiteralExpression('\'value3\'', Type::STRING)
                            ),
                            new ArrayPair(
                                'key2',
                                new LiteralExpression('\'value4\'', Type::STRING)
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with VariableName value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => Property::asVariable('variableName', Type::STRING),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                Property::asVariable('variableName', Type::STRING)
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with ObjectMethodInvocation value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => new MethodInvocation(
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: Type::STRING,
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        ),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                new MethodInvocation(
                                    methodName: 'methodName',
                                    arguments: new MethodArguments(),
                                    mightThrow: false,
                                    type: Type::STRING,
                                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
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
                    'name' => new MethodInvocation(
                        methodName: 'dataName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: Type::STRING,
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ),
                    'data' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'name',
                        new MethodInvocation(
                            methodName: 'dataName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: Type::STRING,
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
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
