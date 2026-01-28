<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class EncapsulatedExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = LiteralExpression::string('"literal"');
        $encapsulatedExpression = new EncapsulatedExpression($expression);

        $this->assertEquals($expression->getMetadata(), $encapsulatedExpression->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(EncapsulatedExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'literal' => [
                'expression' => new EncapsulatedExpression(
                    LiteralExpression::integer(100)
                ),
                'expectedString' => '(100)',
            ],
            'comparison' => [
                'expression' => new EncapsulatedExpression(
                    new ComparisonExpression(
                        LiteralExpression::void('$array[$index]'),
                        LiteralExpression::null(),
                        '??'
                    )
                ),
                'expectedString' => '($array[$index] ?? null)',
            ],
        ];
    }
}
