<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Body;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
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
}
