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
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
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
        $expression = new ClosureExpression($body, TypeCollection::string());

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
                    new Statement(LiteralExpression::integer(5)),
                    new Statement(LiteralExpression::string('"string"')),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'non-empty, has metadata' => [
                'body' => new Body([
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
                    ),
                    new Statement(
                        new ReturnExpression(
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
                'expression' => new ClosureExpression(new Body([]), TypeCollection::string()),
                'expectedString' => '(function () {' . "\n"
                    . '' . "\n"
                    . '})()',
            ],
            'single literal statement' => [
                'expression' => new ClosureExpression(
                    new Body([
                        new Statement(
                            new ReturnExpression(LiteralExpression::integer(5))
                        ),
                    ]),
                    TypeCollection::integer(),
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    return 5;' . "\n"
                    . '})()',
            ],
            'single literal statement, with return statement expression cast to string' => [
                'expression' => new ClosureExpression(
                    body: new Body([
                        new Statement(
                            new ReturnExpression(
                                new CastExpression(
                                    LiteralExpression::integer(5),
                                    Type::STRING
                                )
                            )
                        ),
                    ]),
                    type: TypeCollection::integer(),
                ),
                'expectedString' => '(function () {' . "\n"
                    . '    return (string) 5;' . "\n"
                    . '})()',
            ],
            'multiple literal statements' => [
                'expression' => new ClosureExpression(
                    body: new Body([
                        new Statement(LiteralExpression::integer(3)),
                        new Statement(LiteralExpression::integer(4)),
                        new EmptyLine(),
                        new Statement(
                            new ReturnExpression(LiteralExpression::integer(5))
                        ),
                    ]),
                    type: TypeCollection::integer(),
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
                    body: new Body([
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
                        ),
                        new EmptyLine(),
                        new Statement(
                            new ReturnExpression(
                                new CompositeExpression(
                                    [
                                        new EncapsulatingCastExpression(
                                            new MethodInvocation(
                                                methodName: 'getWidth',
                                                arguments: new MethodArguments(),
                                                mightThrow: false,
                                                type: TypeCollection::integer(),
                                                parent: Property::asObjectVariable('variable'),
                                            ),
                                            Type::STRING,
                                        ),
                                        LiteralExpression::void(' . \'x\' . '),
                                        new EncapsulatingCastExpression(
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
                                    TypeCollection::string(),
                                )
                            )
                        ),
                    ]),
                    type: TypeCollection::string(),
                ),
                '(function () {' . "\n"
                . '    $variable = {{ CLIENT }}->dependencyMethodName();' . "\n"
                . "\n"
                . '    return (string) ($variable->getWidth()) . \'x\' . (string) ($variable->getHeight());' . "\n"
                . '})()',
            ],
            'try/catch block' => [
                'expression' => new ClosureExpression(
                    body: new TryCatchBlock(
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
                    ),
                    type: TypeCollection::string(),
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
                    body: new Body([
                        new Statement(
                            new AssignmentExpression(
                                Property::asStringVariable('variableName'),
                                LiteralExpression::string('"literal value"')
                            )
                        ),
                        new EmptyLine(),
                        new Statement(
                            new ReturnExpression(
                                Property::asStringVariable('variableName')
                            )
                        ),
                    ]),
                    type: TypeCollection::string(),
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
