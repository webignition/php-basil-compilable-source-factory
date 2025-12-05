<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayKey;

class ArrayKeyTest extends TestCase
{
    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(ArrayKey $key, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $key);
    }

    /**
     * @return array<mixed>
     */
    public function toStringDataProvider(): array
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
