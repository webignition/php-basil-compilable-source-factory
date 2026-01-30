<?php

declare(strict_types=1);

namespace Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class TypeCollectionTest extends AbstractResolvableTestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRender(TypeCollection $types, string $expected): void
    {
        $this->assertRenderResolvable($expected, $types);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'string' => [
                'types' => TypeCollection::string(),
                'expected' => 'string',
            ],
            'int, string' => [
                'types' => new TypeCollection([Type::INTEGER, Type::STRING]),
                'expected' => 'int|string',
            ],
            'string, int' => [
                'types' => new TypeCollection([Type::STRING, Type::INTEGER]),
                'expected' => 'int|string',
            ],
        ];
    }

    #[DataProvider('equalsDataProvider')]
    public function testEquals(TypeCollection $collection1, TypeCollection $collection2, bool $expected): void
    {
        self::assertSame($expected, $collection1->equals($collection2));
    }

    /**
     * @return array<mixed>
     */
    public static function equalsDataProvider(): array
    {
        return [
            'both contain same single type' => [
                'collection1' => new TypeCollection([Type::STRING]),
                'collection2' => new TypeCollection([Type::STRING]),
                'expected' => true,
            ],
            'both contain different single type (1)' => [
                'collection1' => new TypeCollection([Type::INTEGER]),
                'collection2' => new TypeCollection([Type::STRING]),
                'expected' => false,
            ],
            'both contain different single type (2)' => [
                'collection1' => new TypeCollection([Type::STRING]),
                'collection2' => new TypeCollection([Type::INTEGER]),
                'expected' => false,
            ],
            'both contain different multiple types' => [
                'collection1' => new TypeCollection([Type::STRING, Type::INTEGER, Type::BOOLEAN]),
                'collection2' => new TypeCollection([Type::INTEGER, Type::BOOLEAN, Type::NULL]),
                'expected' => false,
            ],
        ];
    }
}
