<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\Annotation\DataProviderAnnotation;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassBody;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\DocBlock\DocBlock;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableName;

class ClassBodyTest extends AbstractResolvableTest
{
    /**
     * @dataProvider createDataProvider
     *
     * @param array<mixed>                $methods
     * @param MethodDefinitionInterface[] $expectedMethods
     */
    public function testCreate(array $methods, array $expectedMethods): void
    {
        $body = new ClassBody($methods);

        self::assertEquals($expectedMethods, $body->getMethods());
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no methods' => [
                'methods' => [],
                'expectedMethods' => [],
            ],
            'invalid methods' => [
                'methods' => [
                    1,
                    true,
                    'string',
                ],
                'expectedMethods' => [],
            ],
            'valid methods' => [
                'methods' => [
                    new MethodDefinition('methodOne', new Body([])),
                    new MethodDefinition('methodTwo', new Body([])),
                ],
                'expectedMethods' => [
                    'methodOne' => new MethodDefinition('methodOne', new Body([])),
                    'methodTwo' => new MethodDefinition('methodTwo', new Body([])),
                ],
            ],
        ];
    }

    /**
     * @dataProvider getMetadataDataProvider
     */
    public function testGetMetadata(ClassBody $body, MetadataInterface $expectedMetadata): void
    {
        $this->assertEquals($expectedMetadata, $body->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function getMetadataDataProvider(): array
    {
        return [
            'empty' => [
                'classBody' => new ClassBody([
                    new MethodDefinition('methodName', new Body([])),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'methods without metadata' => [
                'classBody' => new ClassBody([
                    new MethodDefinition('name', new Body([
                        new EmptyLine(),
                        new SingleLineComment('single line comment'),
                    ])),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'methods with metadata' => [
                'classBody' => new ClassBody([
                    new MethodDefinition('name', new Body([
                        new Statement(
                            new ObjectMethodInvocation(
                                new VariableDependency('DEPENDENCY'),
                                'methodName'
                            )
                        ),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('variable'),
                                new MethodInvocation('methodName')
                            )
                        )
                    ])),
                ]),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'DEPENDENCY',
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ClassBody $body, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $body);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'no methods' => [
                'classBody' => new ClassBody([]),
                'expectedString' => '',
            ],
            'single empty method' => [
                'classBody' => new ClassBody([
                    new MethodDefinition('methodName', new Body([])),
                ]),
                'expectedString' => 'public function methodName()' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'many methods' => [
                'classBody' => new ClassBody([
                    new MethodDefinition('stepOne', new Body([
                        new SingleLineComment('click $"a"'),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('statement'),
                                new StaticObjectMethodInvocation(
                                    new StaticObject('Acme\\Statement'),
                                    'createAction',
                                    new MethodArguments([
                                        new LiteralExpression('\'$"a" exists\''),
                                    ])
                                )
                            )
                        ),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('currentStatement'),
                                new VariableName('statement')
                            )
                        ),
                    ])),
                    new MethodDefinition('stepTwo', new Body([
                        new SingleLineComment('click $"b"'),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('statement'),
                                new StaticObjectMethodInvocation(
                                    new StaticObject('Acme\\Statement'),
                                    'createAction',
                                    new MethodArguments([
                                        new LiteralExpression('\'$"b" exists\''),
                                    ])
                                )
                            )
                        ),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('currentStatement'),
                                new VariableName('statement')
                            )
                        ),
                    ])),
                ]),
                'expectedString' => 'public function stepOne()' . "\n" .
                    '{' . "\n" .
                    '    // click $"a"' . "\n" .
                    '    $statement = Statement::createAction(\'$"a" exists\');' . "\n" .
                    '    $currentStatement = $statement;' . "\n" .
                    '}' . "\n" .
                    "\n" .
                    'public function stepTwo()' . "\n" .
                    '{' . "\n" .
                    '    // click $"b"' . "\n" .
                    '    $statement = Statement::createAction(\'$"b" exists\');' . "\n" .
                    '    $currentStatement = $statement;' . "\n" .
                    '}'
            ],
            'many methods, with data provider' => [
                'classBody' => new ClassBody([
                    (function () {
                        $methodDefinition = new MethodDefinition(
                            'stepOne',
                            new Body([
                                new SingleLineComment('click $"a"'),
                                new Statement(
                                    new AssignmentExpression(
                                        new VariableName('statement'),
                                        new StaticObjectMethodInvocation(
                                            new StaticObject('Acme\\Statement'),
                                            'createAction',
                                            new MethodArguments([
                                                new LiteralExpression('\'$"a" exists\''),
                                            ])
                                        )
                                    )
                                ),
                                new Statement(
                                    new AssignmentExpression(
                                        new VariableName('currentStatement'),
                                        new VariableName('statement')
                                    )
                                ),
                            ]),
                            [
                                'x', 'y',
                            ]
                        );

                        $docblock = $methodDefinition->getDocBlock();
                        if ($docblock instanceof DocBlock) {
                            $docblock = $docblock->prepend(new DocBlock([
                                new DataProviderAnnotation('stepOneDataProvider'),
                                "\n",
                            ]));

                            $methodDefinition = $methodDefinition->withDocBlock($docblock);
                        }

                        return $methodDefinition;
                    })(),
                    new DataProviderMethodDefinition('stepOneDataProvider', [
                        0 => [
                            'x' => '1',
                            'y' => '2',
                        ],
                        1 => [
                            'x' => '3',
                            'y' => '4',
                        ],
                    ]),
                    new MethodDefinition('stepTwo', new Body([
                        new SingleLineComment('click $"b"'),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('statement'),
                                new StaticObjectMethodInvocation(
                                    new StaticObject('Acme\\Statement'),
                                    'createAction',
                                    new MethodArguments([
                                        new LiteralExpression('\'$"b" exists\''),
                                    ])
                                )
                            )
                        ),
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('currentStatement'),
                                new VariableName('statement')
                            )
                        ),
                    ])),
                ]),
                'expectedString' => '/**' . "\n" .
                    ' * @dataProvider stepOneDataProvider' . "\n" .
                    ' *' . "\n" .
                    ' * @param string $x' . "\n" .
                    ' * @param string $y' . "\n" .
                    ' */' . "\n" .
                    'public function stepOne($x, $y)' . "\n" .
                    '{' . "\n" .
                    '    // click $"a"' . "\n" .
                    '    $statement = Statement::createAction(\'$"a" exists\');' . "\n" .
                    '    $currentStatement = $statement;' . "\n" .
                    '}' . "\n" .
                    "\n" .
                    'public function stepOneDataProvider(): array' . "\n" .
                    '{' . "\n" .
                    '    return [' . "\n" .
                    '        \'0\' => [' . "\n" .
                    '            \'x\' => \'1\',' . "\n" .
                    '            \'y\' => \'2\',' . "\n" .
                    '        ],' . "\n" .
                    '        \'1\' => [' . "\n" .
                    '            \'x\' => \'3\',' . "\n" .
                    '            \'y\' => \'4\',' . "\n" .
                    '        ],' . "\n" .
                    '    ];' . "\n" .
                    '}' . "\n" .
                    "\n" .
                    'public function stepTwo()' . "\n" .
                    '{' . "\n" .
                    '    // click $"b"' . "\n" .
                    '    $statement = Statement::createAction(\'$"b" exists\');' . "\n" .
                    '    $currentStatement = $statement;' . "\n" .
                    '}'
            ],
        ];
    }
}
