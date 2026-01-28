<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class LiteralExpressionTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(string $content, Type $type): void
    {
        $expression = new LiteralExpression($content, $type);

        $this->assertEquals(new Metadata(), $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'content' => '',
                'type' => Type::STRING,
            ],
            'string' => [
                'content' => '"value"',
                'type' => Type::STRING,
            ],
            'int' => [
                'content' => '1',
                'type' => Type::INTEGER,
            ],
        ];
    }

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
                'expression' => new LiteralExpression('', Type::STRING),
                'expectedString' => '',
            ],
            'string' => [
                'expression' => new LiteralExpression('"value"', Type::STRING),
                'expectedString' => '"value"',
            ],
            'int' => [
                'expression' => new LiteralExpression('2', Type::INTEGER),
                'expectedString' => '2',
            ],
        ];
    }
}
