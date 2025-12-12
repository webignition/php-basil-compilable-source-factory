<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

class ClosureExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(BodyInterface $body, MetadataInterface $expectedMetadata): void
    {
        $expression = new ClosureExpression($body);

        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'body' => new Body([]),
                'expectedMetadata' => new Metadata(),
            ],
            'non-empty, no metadata' => [
                'body' => new Body([
                    new Statement(new LiteralExpression('5')),
                    new Statement(new LiteralExpression('"string"')),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'non-empty, has metadata' => [
                'body' => new Body([
                    new Statement(
                        new AssignmentExpression(
                            new VariableName('variable'),
                            new ObjectMethodInvocation(
                                new VariableDependency(VariableNames::ACTION_FACTORY),
                                'dependencyMethodName'
                            )
                        )
                    ),
                    new Statement(
                        new ReturnExpression(
                            new CompositeExpression([
                                new CastExpression(
                                    new ObjectMethodInvocation(
                                        new VariableName('variable'),
                                        'getWidth'
                                    ),
                                    'string'
                                ),
                                new LiteralExpression(' . \'x\' . '),
                                new CastExpression(
                                    new ObjectMethodInvocation(
                                        new VariableName('variable'),
                                        'getHeight'
                                    ),
                                    'string'
                                ),
                            ])
                        )
                    ),
                ]),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableNames::ACTION_FACTORY,
                    ]
                ),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ClosureExpression $expression, string $expectedString): void
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
                'expression' => new ClosureExpression(new Body([])),
                'expectedString' => '(function () {' . "\n"
                    . '' . "\n"
                    . '})()',
            ],
            'single literal statement' => [
                'expression' => new ClosureExpression(
                    new Body([
                        new Statement(
                            new ReturnExpression(new LiteralExpression('5'))
                        ),
                    ])
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    return 5;' . "\n"
                    . '})()',
            ],
            'single literal statement, with return statement expression cast to string' => [
                'expression' => new ClosureExpression(
                    new Body([
                        new Statement(
                            new ReturnExpression(
                                new CastExpression(
                                    new LiteralExpression('5'),
                                    'string'
                                )
                            )
                        ),
                    ])
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    return (string) (5);' . "\n"
                    . '})()',
            ],
            'multiple literal statements' => [
                'expression' => new ClosureExpression(
                    new Body([
                        new Statement(new LiteralExpression('3')),
                        new Statement(new LiteralExpression('4')),
                        new EmptyLine(),
                        new Statement(
                            new ReturnExpression(new LiteralExpression('5'))
                        ),
                    ])
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    3;' . "\n"
                    . '    4;' . "\n"
                    . "\n"
                    . '    return 5;' . "\n"
                    . '})()',
            ],
            'non-empty, has metadata' => [
                'expression' => new ClosureExpression(
                    new Body([
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('variable'),
                                new ObjectMethodInvocation(
                                    new VariableDependency(VariableNames::ACTION_FACTORY),
                                    'dependencyMethodName'
                                )
                            )
                        ),
                        new EmptyLine(),
                        new Statement(
                            new ReturnExpression(
                                new CompositeExpression([
                                    new CastExpression(
                                        new ObjectMethodInvocation(
                                            new VariableName('variable'),
                                            'getWidth'
                                        ),
                                        'string'
                                    ),
                                    new LiteralExpression(' . \'x\' . '),
                                    new CastExpression(
                                        new ObjectMethodInvocation(
                                            new VariableName('variable'),
                                            'getHeight'
                                        ),
                                        'string'
                                    ),
                                ])
                            )
                        ),
                    ])
                ),
                '(function () {' . "\n"
                . '    $variable = {{ ACTION_FACTORY }}->dependencyMethodName();' . "\n"
                . "\n"
                . '    return (string) ($variable->getWidth()) . \'x\' . (string) ($variable->getHeight());' . "\n"
                . '})()',
            ],
            'try/catch block' => [
                'expression' => new ClosureExpression(
                    new TryCatchBlock(
                        new TryBlock(
                            new Body([
                                new SingleLineComment('TryBlock comment'),
                            ])
                        ),
                        new CatchBlock(
                            new CatchExpression(
                                new ObjectTypeDeclarationCollection([
                                    new ObjectTypeDeclaration(new ClassName(\RuntimeException::class))
                                ])
                            ),
                            new Body([
                                new SingleLineComment('CatchBlock comment'),
                            ])
                        )
                    )
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    try {' . "\n"
                    . '        // TryBlock comment' . "\n"
                    . '    } catch (\RuntimeException $exception) {' . "\n"
                    . '        // CatchBlock comment' . "\n"
                    . '    }' . "\n"
                    . '})()',
            ],
            'with resolving placeholder' => [
                'expression' => new ClosureExpression(
                    new Body([
                        new Statement(
                            new AssignmentExpression(
                                new VariableName('variableName'),
                                new LiteralExpression('"literal value"')
                            )
                        ),
                        new EmptyLine(),
                        new Statement(
                            new ReturnExpression(
                                new VariableName('variableName')
                            )
                        ),
                    ])
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    $variableName = "literal value";' . "\n"
                    . "\n"
                    . '    return $variableName;' . "\n"
                    . '})()',
            ],
        ];
    }
}
