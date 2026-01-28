<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;

class ArgumentFactoryTest extends TestCase
{
    private ArgumentFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ArgumentFactory::createFactory();
    }

    #[DataProvider('createDataProvider')]
    public function testCreate(mixed $argument, ExpressionInterface $expected): void
    {
        self::assertEquals($expected, $this->factory->create($argument));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'integer' => [
                'argument' => 100,
                'expected' => new LiteralExpression('100'),
            ],
            'float' => [
                'argument' => M_PI,
                'expected' => new LiteralExpression((string) M_PI),
            ],
            'string without single quotes' => [
                'argument' => 'string without single quotes',
                'expected' => new LiteralExpression('\'string without single quotes\''),
            ],
            'string with \'single\' quotes' => [
                'argument' => 'string with \'single\' quotes',
                'expected' => new LiteralExpression('\'string with \\\'single\\\' quotes\''),
            ],
            'boolean true' => [
                'argument' => true,
                'expected' => new LiteralExpression('true'),
            ],
            'boolean false' => [
                'argument' => false,
                'expected' => new LiteralExpression('false'),
            ],
            'static object expression' => [
                'argument' => new StaticObject('self'),
                'expected' => new StaticObject('self'),
            ],
            'null' => [
                'argument' => null,
                'expected' => new LiteralExpression('null'),
            ],
        ];
    }
}
