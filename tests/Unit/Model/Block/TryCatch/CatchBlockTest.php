<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\TryCatch;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CatchBlockTest extends AbstractResolvableTestCase
{
    public function testGetMetadata(): void
    {
        $body = new Body([
            new Statement(
                new AssignmentExpression(
                    new VariableDependency(VariableName::PANTHER_CLIENT->value),
                    new ObjectMethodInvocation(
                        object: new StaticObject(\RuntimeException::class),
                        methodName: 'staticMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
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

        $expectedMetadata = new Metadata(
            classNames: [
                \RuntimeException::class,
                \Exception::class,
            ],
            variableNames: [
                VariableName::PANTHER_CLIENT->value,
            ]
        );

        $this->assertEquals($expectedMetadata, $catchBlock->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(CatchBlock $tryBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $tryBlock);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
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
                'expectedString' => 'catch (\Exception $exception) {' . "\n"
                    . '    "literal";' . "\n"
                    . '}',
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
                'expectedString' => 'catch (\LogicException | \RuntimeException $exception) {' . "\n"
                    . '    "literal";' . "\n"
                    . '}',
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
                'expectedString' => 'catch (\LogicException | \RuntimeException | TestCase $exception) {' . "\n"
                    . '    "literal";' . "\n"
                    . '}',
            ],
        ];
    }
}
