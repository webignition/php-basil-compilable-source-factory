<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Body;

use webignition\BasilCompilableSourceFactory\Model\Block\IfBlock\IfBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\ObjectReflector\ObjectReflector;

class BodyTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param BodyContentInterface[] $content
     * @param BodyContentInterface[] $expectedContent
     */
    public function testCreate(array $content, array $expectedContent): void
    {
        $body = new Body($content);

        $this->assertEquals(
            $expectedContent,
            ObjectReflector::getProperty($body, 'content')
        );
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'content' => [],
                'expectedContent' => [],
            ],
            'has content' => [
                'content' => [
                    new \stdClass(),
                    "\n",
                    new SingleLineComment('singe line comment'),
                    true,
                    new EmptyLine(),
                    1,
                    new Statement(
                        new LiteralExpression('"literal from statement"')
                    ),
                    new Body([
                        new Statement(
                            new LiteralExpression('"literal from statement from body"')
                        )
                    ]),
                    new TryCatchBlock(
                        new TryBlock(
                            new Body([
                                new SingleLineComment('TryBlock comment'),
                            ])
                        ),
                        new CatchBlock(
                            new CatchExpression(
                                new ObjectTypeDeclarationCollection([
                                    new ObjectTypeDeclaration(
                                        new ClassName(\LogicException::class)
                                    )
                                ])
                            ),
                            new Body([
                                new SingleLineComment('CatchBlock comment'),
                            ])
                        )
                    ),
                    new Body([]),
                    new IfBlock(
                        new LiteralExpression('true'),
                        new Body([
                            new Statement(
                                new ReturnExpression()
                            )
                        ])
                    )
                ],
                'expectedContent' => [
                    new SingleLineComment('singe line comment'),
                    new EmptyLine(),
                    new Statement(
                        new LiteralExpression('"literal from statement"')
                    ),
                    new Body([
                        new Statement(
                            new LiteralExpression('"literal from statement from body"')
                        )
                    ]),
                    new TryCatchBlock(
                        new TryBlock(
                            new Body([
                                new SingleLineComment('TryBlock comment'),
                            ])
                        ),
                        new CatchBlock(
                            new CatchExpression(
                                new ObjectTypeDeclarationCollection([
                                    new ObjectTypeDeclaration(
                                        new ClassName(\LogicException::class)
                                    )
                                ])
                            ),
                            new Body([
                                new SingleLineComment('CatchBlock comment'),
                            ])
                        )
                    ),
                    new IfBlock(
                        new LiteralExpression('true'),
                        new Body([
                            new Statement(
                                new ReturnExpression()
                            )
                        ])
                    )
                ],
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(Body $body, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $body);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty' => [
                'body' => new Body([]),
                'expectedString' => '',
            ],
            'non-empty' => [
                'body' => new Body([
                    new SingleLineComment('single line comment'),
                    new EmptyLine(),
                    new Statement(
                        new LiteralExpression('"literal from statement"')
                    ),
                    new Body([
                        new Statement(
                            new LiteralExpression('"literal from statement from body"')
                        )
                    ]),
                    new TryCatchBlock(
                        new TryBlock(
                            new Body([
                                new SingleLineComment('TryBlock comment'),
                            ])
                        ),
                        new CatchBlock(
                            new CatchExpression(
                                new ObjectTypeDeclarationCollection([
                                    new ObjectTypeDeclaration(
                                        new ClassName(\LogicException::class)
                                    )
                                ])
                            ),
                            new Body([
                                new SingleLineComment('CatchBlock comment'),
                            ])
                        )
                    ),
                    new IfBlock(
                        new LiteralExpression('true'),
                        new Body([
                            new Statement(
                                new ReturnExpression()
                            )
                        ])
                    ),
                ]),
                'expectedString' => '// single line comment' . "\n"
                    . "\n"
                    . '"literal from statement";' . "\n"
                    . '"literal from statement from body";' . "\n"
                    . 'try {' . "\n"
                    . '    // TryBlock comment' . "\n"
                    . '} catch (\LogicException $exception) {' . "\n"
                    . '    // CatchBlock comment' . "\n"
                    . '}' . "\n"
                    . 'if (true) {' . "\n"
                    . '    return;' . "\n"
                    . '}',
            ],
            'empty return only' => [
                'body' => new Body([
                    new Statement(
                        new ReturnExpression()
                    )
                ]),
                'expectedString' => 'return;',
            ],
            'expression and empty return' => [
                'body' => new Body([
                    new Statement(
                        new LiteralExpression('"literal from statement"')
                    ),
                    new Statement(
                        new ReturnExpression()
                    )
                ]),
                'expectedString' => '"literal from statement";' . "\n"
                    . 'return;',
            ],
            'explicit trailing empty line' => [
                'body' => new Body([
                    new SingleLineComment(
                        'comment 1',
                    ),
                    new EmptyLine(),
                ]),
                'expectedString' => '// comment 1' . "\n",
            ],
            'body containing bodies with explicity trailing empty line' => [
                'body' => new Body([
                    new Body([
                        new SingleLineComment(
                            'comment 1',
                        ),
                        new EmptyLine(),
                    ]),
                    new Body([
                        new SingleLineComment(
                            'comment 2'
                        ),
                    ]),
                ]),
                'expectedString' => '// comment 1' . "\n"
                    . "\n"
                    . '// comment 2',
            ],
        ];
    }

    /**
     * @dataProvider createEnclosingBodyDataProvider
     */
    public function testCreateEnclosingBody(BodyInterface $body, BodyInterface $expectedBody): void
    {
        $this->assertEquals($expectedBody, Body::createEnclosingBody($body));
    }

    /**
     * @return array<mixed>
     */
    public static function createEnclosingBodyDataProvider(): array
    {
        return [
            'enclose a code block' => [
                'body' => new Body([
                    new Statement(
                        new LiteralExpression('"literal')
                    ),
                ]),
                'expectedBody' => new Body([
                    new Statement(
                        new ClosureExpression(
                            new Body([
                                new Statement(
                                    new LiteralExpression('"literal')
                                ),
                            ])
                        )
                    ),
                ]),
            ],
        ];
    }

    public function testCreateFromExpressionsThrowsInvalidArgumentExceptionForNonExpression(): void
    {
        self::expectExceptionObject(new \InvalidArgumentException('Non-expression at index 1'));

        Body::createFromExpressions([
            new LiteralExpression('"literal one"'),
            true,
            new LiteralExpression('"literal two"'),
        ]);
    }

    /**
     * @dataProvider createFromExpressionsDataProvider
     *
     * @param array<mixed> $expressions
     */
    public function testCreateFromExpressions(array $expressions, Body $expectedBody): void
    {
        self::assertEquals($expectedBody, Body::createFromExpressions($expressions));
    }

    /**
     * @return array<mixed>
     */
    public static function createFromExpressionsDataProvider(): array
    {
        return [
            'empty' => [
                'expressions' => [],
                'expectedBody' => new Body([]),
            ],
            'non-empty' => [
                'expressions' => [
                    new LiteralExpression('"literal one"'),
                    new LiteralExpression('"literal two"'),
                ],
                'expectedBody' => new Body([
                    new Statement(
                        new LiteralExpression('"literal one"')
                    ),
                    new Statement(
                        new LiteralExpression('"literal two"')
                    ),
                ]),
            ],
        ];
    }

    public function testCreateForSingleAssignmentStatement(): void
    {
        $variable = new VariableDependency(VariableNames::ACTION_FACTORY);
        $value = new LiteralExpression('"value"');

        $expectedBody = new Body([
            new Statement(
                new AssignmentExpression($variable, $value)
            )
        ]);

        self::assertEquals($expectedBody, Body::createForSingleAssignmentStatement($variable, $value));
    }
}
