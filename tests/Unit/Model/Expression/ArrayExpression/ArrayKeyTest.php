<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression\ArrayExpression;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayKey;

class ArrayKeyTest extends TestCase
{
    #[DataProvider('toStringDataProvider')]
    public function testToString(ArrayKey $key, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $key);
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        return [
            'empty' => [
                'key' => new ArrayKey(''),
                'expectedString' => "''",
            ],
            'non-empty' => [
                'key' => new ArrayKey('key'),
                'expectedString' => "'key'",
            ],
        ];
    }
}
