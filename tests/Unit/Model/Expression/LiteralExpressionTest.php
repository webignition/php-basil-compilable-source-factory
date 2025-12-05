<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class LiteralExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $content): void
    {
        $expression = new LiteralExpression($content);

        $this->assertEquals(new Metadata(), $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'empty' => [
                'content' => '',
            ],
            'string' => [
                'content' => '"value"',
            ],
            'int' => [
                'content' => '1',
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(LiteralExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new LiteralExpression(''),
                'expectedString' => '',
            ],
            'string' => [
                'expression' => new LiteralExpression('"value"'),
                'expectedString' => '"value"',
            ],
            'int' => [
                'expression' => new LiteralExpression('2'),
                'expectedString' => '2',
            ],
        ];
    }
}
