<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\TryCatch;

use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class TryCatchBlockTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(TryCatchBlock $tryCatch, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $tryCatch);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'default' => [
                'tryCatch' => new TryCatchBlock(
                    new TryBlock(
                        new Statement(new MethodInvocation('methodName')),
                    ),
                    new CatchBlock(
                        new CatchExpression(
                            new ObjectTypeDeclarationCollection([
                                new ObjectTypeDeclaration(new ClassName(\LogicException::class)),
                                new ObjectTypeDeclaration(new ClassName(\RuntimeException::class)),
                            ])
                        ),
                        new Body([
                            new SingleLineComment('handle LogicException and RuntimeException')
                        ]),
                    ),
                    new CatchBlock(
                        new CatchExpression(
                            new ObjectTypeDeclarationCollection([
                                new ObjectTypeDeclaration(new ClassName(\LengthException::class)),
                            ])
                        ),
                        new Body([
                            new SingleLineComment('handle LengthException')
                        ])
                    )
                ),
                'expectedString' => 'try {' . "\n" .
                    '    methodName();' . "\n" .
                    '} catch (\LogicException | \RuntimeException $exception) {' . "\n" .
                    '    // handle LogicException and RuntimeException' . "\n" .
                    '} catch (\LengthException $exception) {' . "\n" .
                    '    // handle LengthException' . "\n" .
                    '}',
            ],
        ];
    }
}
