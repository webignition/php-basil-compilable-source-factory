<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTest;

class EncapsulatedExpressionTest extends AbstractResolvableTest
{
    public function testCreate(): void
    {
        $expression = new LiteralExpression('"literal"');
        $encapsulatedExpression = new EncapsulatedExpression($expression);

        $this->assertEquals($expression->getMetadata(), $encapsulatedExpression->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(EncapsulatedExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'literal' => [
                'expression' => new EncapsulatedExpression(
                    new LiteralExpression('100')
                ),
                'expectedString' => '(100)',
            ],
            'comparison' => [
                'expression' => new EncapsulatedExpression(
                    new ComparisonExpression(
                        new LiteralExpression('$array[$index]'),
                        new LiteralExpression('null'),
                        '??'
                    )
                ),
                'expectedString' => '($array[$index] ?? null)',
            ],
        ];
    }
}
