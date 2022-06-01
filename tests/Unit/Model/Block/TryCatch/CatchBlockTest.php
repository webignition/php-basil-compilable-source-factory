<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\TryCatch;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTest;

class CatchBlockTest extends AbstractResolvableTest
{
    public function testGetMetadata(): void
    {
        $body = new Body([
            new Statement(
                new AssignmentExpression(
                    new VariableDependency('DEPENDENCY'),
                    new StaticObjectMethodInvocation(
                        new StaticObject(\RuntimeException::class),
                        'staticMethodName'
                    )
                )
            )
        ]);

        $catchBlock = new CatchBlock(
            new CatchExpression(
                new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                ])
            ),
            $body
        );

        $expectedMetadata = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                new ClassName(\RuntimeException::class),
                new ClassName(\Exception::class),
            ]),
            Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                'DEPENDENCY',
            ]),
        ]);

        $this->assertEquals($expectedMetadata, $catchBlock->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(CatchBlock $tryBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $tryBlock);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'single-class expression, all in root namespace' => [
                'tryBlock' => new CatchBlock(
                    new CatchExpression(
                        new ObjectTypeDeclarationCollection([
                            new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                        ])
                    ),
                    new Statement(
                        new LiteralExpression('"literal"')
                    )
                ),
                'expectedString' => 'catch (\Exception $exception) {' . "\n" .
                    '    "literal";' . "\n" .
                    '}',
            ],
            'multi-class expression, all in root namespace' => [
                'tryBlock' => new CatchBlock(
                    new CatchExpression(
                        new ObjectTypeDeclarationCollection([
                            new ObjectTypeDeclaration(new ClassName(\LogicException::class)),
                            new ObjectTypeDeclaration(new ClassName(\RuntimeException::class)),
                        ])
                    ),
                    new Statement(
                        new LiteralExpression('"literal"')
                    )
                ),
                'expectedString' => 'catch (\LogicException | \RuntimeException $exception) {' . "\n" .
                    '    "literal";' . "\n" .
                    '}',
            ],
            'multi-class expression, not all in root namespace' => [
                'tryBlock' => new CatchBlock(
                    new CatchExpression(
                        new ObjectTypeDeclarationCollection([
                            new ObjectTypeDeclaration(new ClassName(\LogicException::class)),
                            new ObjectTypeDeclaration(new ClassName(\RuntimeException::class)),
                            new ObjectTypeDeclaration(new ClassName(TestCase::class)),
                        ])
                    ),
                    new Statement(
                        new LiteralExpression('"literal"')
                    )
                ),
                'expectedString' => 'catch (\LogicException | \RuntimeException | TestCase $exception) {' . "\n" .
                    '    "literal";' . "\n" .
                    '}',
            ],
            'empty return statement only' => [
                'tryBlock' => new CatchBlock(
                    new CatchExpression(
                        new ObjectTypeDeclarationCollection([
                            new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                        ])
                    ),
                    new Statement(
                        new ReturnExpression()
                    )
                ),
                'expectedString' => 'catch (\Exception $exception) {' . "\n" .
                    '    return;' . "\n" .
                    '}',
            ],
        ];
    }
}
