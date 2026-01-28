<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Body;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\ObjectReflector\ObjectReflector;

class BodyTest extends AbstractResolvableTestCase
{
    /**
     * @param BodyContentInterface[] $content
     * @param BodyContentInterface[] $expectedContent
     */
    #[DataProvider('createDataProvider')]
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
                        LiteralExpression::string('"literal from statement"')
                    ),
                    new Body([
                        new Statement(
                            LiteralExpression::string('"literal from statement from body"')
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
                ],
                'expectedContent' => [
                    new SingleLineComment('singe line comment'),
                    new EmptyLine(),
                    new Statement(
                        LiteralExpression::string('"literal from statement"')
                    ),
                    new Body([
                        new Statement(
                            LiteralExpression::string('"literal from statement from body"')
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
                ],
            ],
        ];
    }

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
                'body' => new Body([]),
                'expectedString' => '',
            ],
            'non-empty' => [
                'body' => new Body([
                    new SingleLineComment('single line comment'),
                    new EmptyLine(),
                    new Statement(
                        LiteralExpression::string('"literal from statement"')
                    ),
                    new Body([
                        new Statement(
                            LiteralExpression::string('"literal from statement from body"')
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
                ]),
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

    public function testCreateFromExpressionsThrowsInvalidArgumentExceptionForNonExpression(): void
    {
        self::expectExceptionObject(new \InvalidArgumentException('Non-expression at index 1'));

        Body::createFromExpressions([
            LiteralExpression::string('"literal one"'),
            true,
            LiteralExpression::string('"literal two"'),
        ]);
    }

    /**
     * @param array<mixed> $expressions
     */
    #[DataProvider('createFromExpressionsDataProvider')]
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
                    LiteralExpression::string('"literal one"'),
                    LiteralExpression::string('"literal two"'),
                ],
                'expectedBody' => new Body([
                    new Statement(
                        LiteralExpression::string('"literal one"')
                    ),
                    new Statement(
                        LiteralExpression::string('"literal two"')
                    ),
                ]),
            ],
        ];
    }

    public function testCreateForSingleAssignmentStatement(): void
    {
        $variable = Property::asDependency(DependencyName::PANTHER_CLIENT);
        $value = LiteralExpression::string('"value"');

        $expectedBody = new Body([
            new Statement(
                new AssignmentExpression($variable, $value)
            )
        ]);

        self::assertEquals($expectedBody, Body::createForSingleAssignmentStatement($variable, $value));
    }
}
