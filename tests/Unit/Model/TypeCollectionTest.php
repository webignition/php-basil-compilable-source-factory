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
}
