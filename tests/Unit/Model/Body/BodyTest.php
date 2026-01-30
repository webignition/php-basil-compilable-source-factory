<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Body;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class BodyTest extends AbstractResolvableTestCase
{
    #[DataProvider('renderDataProvider')]
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
                'body' => new Body(),
                'expectedString' => '',
            ],
            'non-empty' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(
                            new SingleLineComment('single line comment')
                        )
                        ->append(
                            new EmptyLine()
                        )
                        ->append(
                            new Statement(
                                LiteralExpression::string('"literal from statement"')
                            )
                        )
                        ->append(
                            new Body(
                                BodyContentCollection::createFromExpressions([
                                    LiteralExpression::string('"literal from statement from body"'),
                                ])
                            )
                        )
                        ->append(
                            new TryCatchBlock(
                                new TryBlock(
                                    new Body(
                                        new BodyContentCollection()
                                            ->append(new SingleLineComment('TryBlock comment'))
                                    ),
                                ),
                                new CatchBlock(
                                    new CatchExpression(
                                        new ObjectTypeDeclarationCollection([
                                            new ObjectTypeDeclaration(
                                                new ClassName(\LogicException::class)
                                            )
                                        ])
                                    ),
                                    new Body(
                                        new BodyContentCollection()
                                            ->append(new SingleLineComment('CatchBlock comment'))
                                    ),
                                )
                            )
                        )
                ),
                'expectedString' => '// single line comment' . "\n"
                    . "\n"
                    . '"literal from statement";' . "\n"
                    . '"literal from statement from body";' . "\n"
                    . 'try {' . "\n"
                    . '    // TryBlock comment' . "\n"
                    . '} catch (\LogicException $exception) {' . "\n"
                    . '    // CatchBlock comment' . "\n"
                    . '}',
            ],
            'explicit trailing empty line' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(new SingleLineComment('comment 1'))
                        ->append(new EmptyLine())
                ),
                'expectedString' => '// comment 1' . "\n",
            ],
            'body containing bodies with explicit trailing empty line' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(
                            new Body(
                                new BodyContentCollection()
                                    ->append(new SingleLineComment('comment 1'))
                                    ->append(new EmptyLine())
                            ),
                        )
                        ->append(
                            new Body(
                                new BodyContentCollection()
                                    ->append(new SingleLineComment('comment 2'))
                            ),
                        )
                ),
                'expectedString' => '// comment 1' . "\n"
                    . "\n"
                    . '// comment 2',
            ],
        ];
    }

    #[DataProvider('getReturnTypeDataProvider')]
    public function testGetReturnType(Body $body, ?TypeCollection $expected): void
    {
        self::assertEquals($expected, $body->getReturnType());
    }

    /**
     * @return array<mixed>
     */
    public static function getReturnTypeDataProvider(): array
    {
        return [
            'empty' => [
                'body' => new Body(),
                'expected' => null,
            ],
            'single comment' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(
                            new SingleLineComment('comment')
                        )
                ),
                'expected' => null,
            ],
            'single non-returning expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        LiteralExpression::string('"literal"')
                    ])
                ),
                'expected' => null,
            ],
            'single string return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new ReturnExpression(
                            Property::asStringVariable('variable'),
                        )
                    ])
                ),
                'expected' => TypeCollection::string(),
            ],
            'multiple string return expressions' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new ReturnExpression(
                            Property::asStringVariable('variable1'),
                        ),
                        new ReturnExpression(
                            Property::asStringVariable('variable2'),
                        ),
                    ])
                ),
                'expected' => TypeCollection::string(),
            ],
            'multiple mixed-type return expressions' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new ReturnExpression(
                            Property::asObjectVariable('object'),
                        ),
                        new ReturnExpression(
                            Property::asStringVariable('string'),
                        ),
                        new ReturnExpression(
                            Property::asBooleanVariable('boolean'),
                        ),
                        new ReturnExpression(
                            Property::asIntegerVariable('integer'),
                        ),
                        new ReturnExpression(
                            Property::asObjectProperty(
                                Property::asObjectVariable('parent'),
                                'array_typed_property',
                                TypeCollection::array()
                            )
                        ),
                        new ReturnExpression(
                            Property::asObjectProperty(
                                Property::asObjectVariable('parent'),
                                'null_typed_property',
                                TypeCollection::null()
                            )
                        ),
                        new ReturnExpression(
                            Property::asObjectProperty(
                                Property::asObjectVariable('parent'),
                                'void_typed_property',
                                TypeCollection::void()
                            )
                        ),
                    ])
                ),
                'expected' => new TypeCollection([
                    Type::OBJECT,
                    Type::STRING,
                    Type::BOOLEAN,
                    Type::INTEGER,
                    Type::ARRAY,
                    Type::NULL,
                    Type::VOID,
                ]),
            ],
            'single string returning method with no return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new MethodInvocation(
                            'methodName',
                            new MethodArguments([]),
                            false,
                            TypeCollection::string(),
                        ),
                    ])
                ),
                'expected' => null,
            ],
            'single string returning method with return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new ReturnExpression(
                            new MethodInvocation(
                                'methodName',
                                new MethodArguments([]),
                                false,
                                TypeCollection::string(),
                            ),
                        )
                    ])
                ),
                'expected' => TypeCollection::string(),
            ],
            'containing try/catch block with returning try block' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(
                            new TryCatchBlock(
                                new TryBlock(
                                    new Body(
                                        BodyContentCollection::createFromExpressions([
                                            new ReturnExpression(
                                                Property::asStringVariable('string'),
                                            ),
                                        ])
                                    )
                                ),
                                new CatchBlock(
                                    new CatchExpression(
                                        new ObjectTypeDeclarationCollection([
                                            new ObjectTypeDeclaration(
                                                new ClassName(\Throwable::class)
                                            )
                                        ])
                                    ),
                                    new Body()
                                ),
                            ),
                        )
                ),
                'expected' => TypeCollection::string(),
            ],
            'containing try/catch block with returning try block and catch blocks' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(
                            new TryCatchBlock(
                                new TryBlock(
                                    new Body(
                                        BodyContentCollection::createFromExpressions([
                                            new ReturnExpression(
                                                Property::asStringVariable('string'),
                                            ),
                                        ])
                                    )
                                ),
                                new CatchBlock(
                                    new CatchExpression(
                                        new ObjectTypeDeclarationCollection([
                                            new ObjectTypeDeclaration(
                                                new ClassName(\Throwable::class)
                                            )
                                        ])
                                    ),
                                    new Body(
                                        BodyContentCollection::createFromExpressions([
                                            new ReturnExpression(
                                                Property::asIntegerVariable('integer'),
                                            ),
                                        ])
                                    )
                                ),
                                new CatchBlock(
                                    new CatchExpression(
                                        new ObjectTypeDeclarationCollection([
                                            new ObjectTypeDeclaration(
                                                new ClassName(\Throwable::class)
                                            )
                                        ])
                                    ),
                                    new Body(
                                        BodyContentCollection::createFromExpressions([
                                            new ReturnExpression(
                                                Property::asBooleanVariable('boolean'),
                                            ),
                                        ])
                                    )
                                ),
                            ),
                        )
                ),
                'expected' => new TypeCollection([
                    Type::STRING,
                    Type::INTEGER,
                    Type::BOOLEAN,
                ]),
            ],
            'containing closure expression with no return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new ClosureExpression(
                            new Body(
                                BodyContentCollection::createFromExpressions([
                                    Property::asStringVariable('string'),
                                ])
                            )
                        )
                    ])
                ),
                'expected' => null,
            ],
            'containing closure expression with return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new ClosureExpression(
                            new Body(
                                BodyContentCollection::createFromExpressions([
                                    new ReturnExpression(
                                        Property::asStringVariable('string'),
                                    )
                                ])
                            ),
                        )
                    ])
                ),
                'expected' => TypeCollection::string(),
            ],
            'containing encapsulated expression with no return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new EncapsulatedExpression(
                            Property::asStringVariable('string'),
                        ),
                    ])
                ),
                'expected' => null,
            ],
            'containing encapsulated expression with return expression' => [
                'body' => new Body(
                    BodyContentCollection::createFromExpressions([
                        new EncapsulatedExpression(
                            new ReturnExpression(
                                Property::asStringVariable('string')
                            ),
                        ),
                    ])
                ),
                'expected' => TypeCollection::string(),
            ],
        ];
    }
}
