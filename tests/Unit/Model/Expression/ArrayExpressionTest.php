<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayKey;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayPair;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ArrayExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider getMetadataDataProvider
     */
    public function testGetMetadata(ArrayExpression $expression, MetadataInterface $expectedMetadata): void
    {
        self::assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function getMetadataDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new ArrayExpression([]),
                'expectedMetadata' => new Metadata(),
            ],
            'no metadata' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('key1'),
                        new LiteralExpression('\'value1\'')
                    ),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('key3'),
                        new ObjectMethodInvocation(
                            new VariableDependency('OBJECT'),
                            'methodName'
                        )
                    ),
                ]),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'OBJECT',
                    ])
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ArrayExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new ArrayExpression([]),
                'expectedString' => '[]',
            ],
            'single pair' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('key1'),
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
                        new ArrayKey('key1'),
                        new LiteralExpression('\'value1\'')
                    ),
                    new ArrayPair(
                        new ArrayKey('key2'),
                        new VariableName('variableName')
                    ),
                    new ArrayPair(
                        new ArrayKey('key3'),
                        new ObjectMethodInvocation(
                            new VariableDependency('OBJECT'),
                            'methodName'
                        )
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'key1' => 'value1',\n"
                    . "    'key2' => \$variableName,\n"
                    . "    'key3' => {{ OBJECT }}->methodName(),\n"
                    . ']',
            ],
            'single data set with single key:value numerical name' => [
                'expression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('0'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
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
                        new ArrayKey('data-set-one'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
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
                        new ArrayKey('0'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                new ArrayKey('key2'),
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
                        new ArrayKey('0'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                new ArrayKey('key2'),
                                new LiteralExpression('\'value2\'')
                            ),
                        ])
                    ),
                    new ArrayPair(
                        new ArrayKey('1'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new LiteralExpression('\'value3\'')
                            ),
                            new ArrayPair(
                                new ArrayKey('key2'),
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
                        new ArrayKey('data-set-one'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new VariableName('variableName')
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
                        new ArrayKey('data-set-one'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new ObjectMethodInvocation(
                                    new VariableDependency('OBJECT'),
                                    'methodName'
                                )
                            ),
                        ])
                    ),
                ]),
                'expectedString' => "[\n"
                    . "    'data-set-one' => [\n"
                    . "        'key1' => {{ OBJECT }}->methodName(),\n"
                    . "    ],\n"
                    . ']',
            ],
        ];
    }

    /**
     * @dataProvider fromArrayDataProvider
     */
    public function testFromArray(ArrayExpression $expression, ArrayExpression $expectedExpression): void
    {
        self::assertEquals($expectedExpression, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function fromArrayDataProvider(): array
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
                        new ArrayKey('0'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
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
                        new ArrayKey('data-set-one'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
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
                        new ArrayKey('0'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                new ArrayKey('key2'),
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
                        new ArrayKey('0'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new LiteralExpression('\'value1\'')
                            ),
                            new ArrayPair(
                                new ArrayKey('key2'),
                                new LiteralExpression('\'value2\'')
                            ),
                        ])
                    ),
                    new ArrayPair(
                        new ArrayKey('1'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new LiteralExpression('\'value3\'')
                            ),
                            new ArrayPair(
                                new ArrayKey('key2'),
                                new LiteralExpression('\'value4\'')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with VariableName value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => new VariableName('variableName'),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('data-set-one'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new VariableName('variableName')
                            ),
                        ])
                    ),
                ]),
            ],
            'single data set with ObjectMethodInvocation value' => [
                'expression' => ArrayExpression::fromArray([
                    'data-set-one' => [
                        'key1' => new ObjectMethodInvocation(
                            new VariableDependency('OBJECT'),
                            'methodName'
                        ),
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('data-set-one'),
                        new ArrayExpression([
                            new ArrayPair(
                                new ArrayKey('key1'),
                                new ObjectMethodInvocation(
                                    new VariableDependency('OBJECT'),
                                    'methodName'
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
                        new ArrayKey('data'),
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
                        new VariableDependency('DEPENDENCY'),
                        'dataName'
                    ),
                    'data' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                ]),
                'expectedExpression' => new ArrayExpression([
                    new ArrayPair(
                        new ArrayKey('name'),
                        new ObjectMethodInvocation(
                            new VariableDependency('DEPENDENCY'),
                            'dataName'
                        )
                    ),
                    new ArrayPair(
                        new ArrayKey('data'),
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
