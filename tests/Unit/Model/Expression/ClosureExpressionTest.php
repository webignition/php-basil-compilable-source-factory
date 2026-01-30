<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\ReturnStatement;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ClosureExpressionTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
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
                'body' => new Body(),
                'expectedMetadata' => new Metadata(),
            ],
            'non-empty, no metadata' => [
                'body' => new Body(BodyContentCollection::createFromExpressions([
                    LiteralExpression::integer(5),
                    LiteralExpression::string('"string"'),
                ])),
                'expectedMetadata' => new Metadata(),
            ],
            'non-empty, has metadata' => [
                'body' => new Body(
                    new BodyContentCollection()
                        ->append(
                            new Statement(
                                new AssignmentExpression(
                                    Property::asStringVariable('variable'),
                                    new MethodInvocation(
                                        methodName: 'dependencyMethodName',
                                        arguments: new MethodArguments(),
                                        mightThrow: false,
                                        type: TypeCollection::string(),
                                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                                    )
                                )
                            )
                        )
                        ->append(
                            new ReturnStatement(
                                new CompositeExpression(
                                    [
                                        new CastExpression(
                                            new MethodInvocation(
                                                methodName: 'getWidth',
                                                arguments: new MethodArguments(),
                                                mightThrow: false,
                                                type: TypeCollection::integer(),
                                                parent: Property::asObjectVariable('variable'),
                                            ),
                                            Type::STRING
                                        ),
                                        LiteralExpression::void(' . \'x\' . '),
                                        new CastExpression(
                                            new MethodInvocation(
                                                methodName: 'getHeight',
                                                arguments: new MethodArguments(),
                                                mightThrow: false,
                                                type: TypeCollection::integer(),
                                                parent: Property::asObjectVariable('variable'),
                                            ),
                                            Type::STRING
                                        ),
                                    ],
                                    TypeCollection::integer(),
                                )
                            )
                        )
                ),
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
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
                'expression' => new ClosureExpression(new Body()),
                'expectedString' => <<<'EOD'
                    (function (): void {
                    
                    })()
                    EOD,
            ],
            'single literal statement' => [
                'expression' => new ClosureExpression(
                    new Body(
                        new BodyContentCollection()
                            ->append(
                                new ReturnStatement(LiteralExpression::integer(5))
                            ),
                    ),
                ),
                'expectedString' => <<<'EOD'
                    (function (): int {
                        return 5;
                    })()
                    EOD,
            ],
            'single literal statement, with return statement expression cast to string' => [
                'expression' => new ClosureExpression(
                    new Body(
                        new BodyContentCollection()
                            ->append(
                                new ReturnStatement(
                                    new CastExpression(
                                        LiteralExpression::integer(5),
                                        Type::STRING
                                    )
                                )
                            ),
                    ),
                ),
                'expectedString' => <<<'EOD'
                    (function (): string {
                        return (string) 5;
                    })()
                    EOD,
            ],
            'multiple literal statements' => [
                'expression' => new ClosureExpression(
                    new Body(
                        new BodyContentCollection()
                            ->append(
                                new Statement(LiteralExpression::integer(3))
                            )
                            ->append(
                                new Statement(LiteralExpression::integer(4))
                            )
                            ->append(
                                new EmptyLine()
                            )
                            ->append(
                                new ReturnStatement(LiteralExpression::integer(5))
                            )
                    ),
                ),
                'expectedString' => <<<'EOD'
                    (function (): int {
                        3;
                        4;
                    
                        return 5;
                    })()
                    EOD,
            ],
            'non-empty, has metadata' => [
                'expression' => new ClosureExpression(
                    new Body(
                        new BodyContentCollection()
                            ->append(
                                new Statement(
                                    new AssignmentExpression(
                                        Property::asObjectVariable('variable'),
                                        new MethodInvocation(
                                            methodName: 'dependencyMethodName',
                                            arguments: new MethodArguments(),
                                            mightThrow: false,
                                            type: TypeCollection::string(),
                                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                                        )
                                    )
                                )
                            )
                            ->append(
                                new EmptyLine()
                            )
                            ->append(
                                new ReturnStatement(
                                    new CompositeExpression(
                                        [
                                            new CastExpression(
                                                new EncapsulatedExpression(
                                                    new MethodInvocation(
                                                        methodName: 'getWidth',
                                                        arguments: new MethodArguments(),
                                                        mightThrow: false,
                                                        type: TypeCollection::integer(),
                                                        parent: Property::asObjectVariable('variable'),
                                                    ),
                                                ),
                                                Type::STRING,
                                            ),
                                            LiteralExpression::void(' . \'x\' . '),
                                            new CastExpression(
                                                new EncapsulatedExpression(
                                                    new MethodInvocation(
                                                        methodName: 'getHeight',
                                                        arguments: new MethodArguments(),
                                                        mightThrow: false,
                                                        type: TypeCollection::integer(),
                                                        parent: Property::asObjectVariable('variable'),
                                                    ),
                                                ),
                                                Type::STRING
                                            ),
                                        ],
                                        TypeCollection::string(),
                                    )
                                )
                            )
                    ),
                ),
                'expectedString' => <<<'EOD'
                    (function (): string {
                        $variable = {{ CLIENT }}->dependencyMethodName();

                        return (string) ($variable->getWidth()) . 'x' . (string) ($variable->getHeight());
                    })()
                    EOD,
            ],
            'try/catch block' => [
                'expression' => new ClosureExpression(
                    new Body(
                        new BodyContentCollection()
                            ->append(
                                new TryCatchBlock(
                                    new TryBlock(
                                        new Body(
                                            new BodyContentCollection()
                                                ->append(new SingleLineComment('TryBlock comment'))
                                        ),
                                    ),
                                    new CatchBlock(
                                        new ObjectTypeDeclarationCollection([
                                            new ObjectTypeDeclaration(new ClassName(\RuntimeException::class))
                                        ]),
                                        new Body(
                                            new BodyContentCollection()
                                                ->append(new SingleLineComment('CatchBlock comment'))
                                        ),
                                    )
                                )
                            )
                    ),
                ),
                'expectedString' => <<<'EOD'
                    (function (): void {
                        try {
                            // TryBlock comment
                        } catch (\RuntimeException $exception) {
                            // CatchBlock comment
                        }
                    })()
                    EOD,
            ],
            'with resolving placeholder' => [
                'expression' => new ClosureExpression(
                    new Body(
                        new BodyContentCollection()
                            ->append(
                                new Statement(
                                    new AssignmentExpression(
                                        Property::asStringVariable('variableName'),
                                        LiteralExpression::string('"literal value"')
                                    )
                                )
                            )
                            ->append(
                                new EmptyLine()
                            )
                            ->append(
                                new ReturnStatement(
                                    Property::asStringVariable('variableName')
                                )
                            )
                    ),
                ),
                'expectedString' => <<<'EOD'
                    (function (): string {
                        $variableName = "literal value";

                        return $variableName;
                    })()
                    EOD,
            ],
        ];
    }
}
