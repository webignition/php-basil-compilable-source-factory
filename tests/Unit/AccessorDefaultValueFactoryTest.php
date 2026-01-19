<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;

class AccessorDefaultValueFactoryTest extends TestCase
{
    private AccessorDefaultValueFactory $accessorDefaultValueFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessorDefaultValueFactory = AccessorDefaultValueFactory::createFactory();
    }

    #[DataProvider('createIntegerDataProvider')]
    public function testCreateInteger(string $value, ?int $expectedDefaultValue): void
    {
        $this->assertSame($expectedDefaultValue, $this->accessorDefaultValueFactory->createInteger($value));
    }

    /**
     * @return array<mixed>
     */
    public static function createIntegerDataProvider(): array
    {
        return [
            'not environment value' => [
                'value' => '10',
                'expectedDefaultValue' => null,
            ],
            'environment value, no default' => [
                'value' => '$env.DURATION',
                'expectedDefaultValue' => null,
            ],
            'environment value, integer default' => [
                'value' => '$env.DURATION|3',
                'expectedDefaultValue' => 3,
            ],
            'environment value, integer-string default' => [
                'value' => '$env.DURATION|"5"',
                'expectedDefaultValue' => 5,
            ],
        ];
    }

    #[DataProvider('createStringDataProvider')]
    public function testCreateString(string $value, ?string $expectedDefaultValue): void
    {
        $this->assertSame($expectedDefaultValue, $this->accessorDefaultValueFactory->createString($value));
    }

    /**
     * @return array<mixed>
     */
    public static function createStringDataProvider(): array
    {
        return [
            'not environment value' => [
                'value' => '10',
                'expectedDefaultValue' => null,
            ],
            'environment value, no default' => [
                'value' => '$env.DURATION',
                'expectedDefaultValue' => null,
            ],
            'environment value, integer default' => [
                'value' => '$env.DURATION|3',
                'expectedDefaultValue' => "'3'",
            ],
            'environment value, integer-string default' => [
                'value' => '$env.DURATION|"5"',
                'expectedDefaultValue' => "'5'",
            ],
            'environment value, string default' => [
                'value' => '$env.DURATION|"foo"',
                'expectedDefaultValue' => "'foo'",
            ],
        ];
    }
}
