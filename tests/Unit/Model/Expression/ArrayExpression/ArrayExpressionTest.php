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
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
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
                        LiteralExpression::string('\'value1\'')
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
                            type: TypeCollection::string(),
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
    public function testRender(ArrayExpression $expression, string $expected): void
    {
        $this->assertRenderResolvable($expected, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new ArrayExpression([]),
                'expected' => '[]',
            ],
            'single pair' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key1',
                        LiteralExpression::string('\'value1\'')
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        'key1' => 'value1',
                    ]
                    EOD,
            ],
            'multiple pairs, render with keys' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'key1',
                        LiteralExpression::string('\'value1\'')
                    ),
                    new ArrayPair(
                        'key2',
                        Property::asStringVariable('variableName')
                    ),
                    new ArrayPair(
                        'key3',
                        new MethodInvocation(
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: TypeCollection::string(),
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        )
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        'key1' => 'value1',
                        'key2' => $variableName,
                        'key3' => {{ CLIENT }}->methodName(),
                    ]
                    EOD,
            ],
            'multiple pairs, render without keys' => [
                'expression' => new ArrayExpression(
                    pairs: [
                        new ArrayPair(
                            '0',
                            LiteralExpression::string('\'value0\'')
                        ),
                        new ArrayPair(
                            '1',
                            LiteralExpression::string('\'value1\'')
                        ),
                    ],
                    renderKeys: false,
                ),
                'expected' => <<<'EOD'
                    [
                        'value0',
                        'value1',
                    ]
                    EOD,
            ],
            'single data set with single key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                LiteralExpression::string('\'value1\'')
                            ),
                        ])
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        '0' => [
                            'key1' => 'value1',
                        ],
                    ]
                    EOD,
            ],
            'single data set with single key:value string name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                LiteralExpression::string('\'value1\'')
                            ),
                        ])
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        'data-set-one' => [
                            'key1' => 'value1',
                        ],
                    ]
                    EOD,
            ],
            'single data set with multiple key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                LiteralExpression::string('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                LiteralExpression::string('\'value2\'')
                            ),
                        ])
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        '0' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ],
                    ]
                    EOD,
            ],
            'multiple data sets with multiple key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        '0',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                LiteralExpression::string('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                LiteralExpression::string('\'value2\'')
                            ),
                        ])
                    ),
                    new ArrayPair(
                        '1',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                LiteralExpression::string('\'value3\'')
                            ),
                            new ArrayPair(
                                'key2',
                                LiteralExpression::string('\'value4\'')
                            ),
                        ])
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        '0' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ],
                        '1' => [
                            'key1' => 'value3',
                            'key2' => 'value4',
                        ],
                    ]
                    EOD,
            ],

            'single data set with VariableName value' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                Property::asStringVariable('variableName')
                            ),
                        ])
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        'data-set-one' => [
                            'key1' => $variableName,
                        ],
                    ]
                    EOD,
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
                                    type: TypeCollection::string(),
                                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                                )
                            ),
                        ])
                    ),
                ]),
                'expected' => <<<'EOD'
                    [
                        'data-set-one' => [
                            'key1' => {{ CLIENT }}->methodName(),
                        ],
                    ]
                    EOD,
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
                                LiteralExpression::string('\'value1\'')
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
                                LiteralExpression::string('\'value1\'')
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
                                LiteralExpression::string('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                LiteralExpression::string('\'value2\'')
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
                                LiteralExpression::string('\'value1\'')
                            ),
                            new ArrayPair(
                                'key2',
                                LiteralExpression::string('\'value2\'')
                            ),
                        ])
                    ),
                    new ArrayPair(
                        '1',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                LiteralExpression::string('\'value3\'')
                            ),
                            new ArrayPair(
                                'key2',
                                LiteralExpression::string('\'value4\'')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with VariableName value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => Property::asStringVariable('variableName'),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        'data-set-one',
                        new ArrayExpression([
                            new ArrayPair(
                                'key1',
                                Property::asStringVariable('variableName')
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
                            type: TypeCollection::string(),
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
                                    type: TypeCollection::string(),
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
                        type: TypeCollection::string(),
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
                            type: TypeCollection::string(),
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
