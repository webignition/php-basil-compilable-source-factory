<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\TryCatch;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class TryBlockTest extends AbstractResolvableTestCase
{
    public function testGetMetadata(): void
    {
        $body = new Body([
            new Statement(
                new AssignmentExpression(
                    new VariableDependency(VariableName::PANTHER_CLIENT),
                    new ObjectMethodInvocation(
                        object: new StaticObject(\RuntimeException::class),
                        methodName: 'staticMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    )
                )
            ),
        ]);

        $tryBlock = new TryBlock($body);

        $expectedMetadata = new Metadata(
            classNames: [
                \RuntimeException::class,
            ],
            variableNames: [
                VariableName::PANTHER_CLIENT,
            ]
        );

        $this->assertEquals($expectedMetadata, $tryBlock->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(TryBlock $tryBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $tryBlock);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'default' => [
                'tryBlock' => new TryBlock(
                    new Statement(
                        new LiteralExpression('"literal expression"')
                    )
                ),
                'expectedString' => 'try {' . "\n"
                    . '    "literal expression";' . "\n"
                    . '}',
            ],
        ];
    }
}
