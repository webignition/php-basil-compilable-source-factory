<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class LiteralExpressionTest extends AbstractResolvableTestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRender(LiteralExpression $expression, string $expectedString): void
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
                'expression' => LiteralExpression::string(''),
                'expectedString' => '',
            ],
            'string' => [
                'expression' => LiteralExpression::string('"value"'),
                'expectedString' => '"value"',
            ],
            'int' => [
                'expression' => LiteralExpression::integer(2),
                'expectedString' => '2',
            ],
        ];
    }
}
