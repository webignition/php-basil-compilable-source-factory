<?php

declare(strict_types=1);

namespace Unit\Model\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Statement\ReturnStatement;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ReturnStatementTest extends AbstractResolvableTestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRender(ReturnStatement $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'return an expression' => [
                'expression' => new ReturnStatement(
                    LiteralExpression::integer(100)
                ),
                'expectedString' => 'return 100;',
            ],
        ];
    }
}
