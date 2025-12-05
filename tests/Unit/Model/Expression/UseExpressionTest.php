<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\UseExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class UseExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = new UseExpression(new ClassName(UseExpressionTest::class));

        $this->assertEquals(new Metadata(), $expression->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(UseExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'no alias' => [
                'expression' => new UseExpression(new ClassName(TestCase::class)),
                'expectedString' => 'use PHPUnit\Framework\TestCase',
            ],
            'has alias' => [
                'expression' => new UseExpression(new ClassName(TestCase::class, 'AliasName')),
                'expectedString' => 'use PHPUnit\Framework\TestCase as AliasName',
            ],
        ];
    }
}
