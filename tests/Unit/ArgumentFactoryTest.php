<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

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

    /**
     * @dataProvider createDataProvider
     *
     * @param array<mixed>          $arguments
     * @param ExpressionInterface[] $expectedArguments
     */
    public function testCreate(array $arguments, array $expectedArguments): void
    {
        self::assertEquals($expectedArguments, $this->factory->create(...$arguments));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'arguments' => [],
                'expectedArguments' => [],
            ],
            'non-empty' => [
                'arguments' => [
                    100,
                    M_PI,
                    'string without single quotes',
                    'string with \'single\' quotes',
                    true,
                    false,
                    new \stdClass(),
                    new StaticObject('self'),
                    null,
                ],
                'expectedArguments' => [
                    new LiteralExpression('100'),
                    new LiteralExpression((string) M_PI),
                    new LiteralExpression('\'string without single quotes\''),
                    new LiteralExpression('\'string with \\\'single\\\' quotes\''),
                    new LiteralExpression('true'),
                    new LiteralExpression('false'),
                    new StaticObject('self'),
                    new LiteralExpression('null'),
                ],
            ],
        ];
    }
}
