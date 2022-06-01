<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTest;

class ReturnExpressionTest extends AbstractResolvableTest
{
    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ReturnExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'empty return' => [
                'expression' => new ReturnExpression(),
                'expectedString' => 'return',
            ],
            'return an expression' => [
                'expression' => new ReturnExpression(
                    new LiteralExpression('100')
                ),
                'expectedString' => 'return 100',
            ],
        ];
    }
}
