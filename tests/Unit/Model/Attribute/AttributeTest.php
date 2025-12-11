<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Attribute\Attribute;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class AttributeTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(Attribute $attribute, Metadata $expected): void
    {
        self::assertEquals($expected, $attribute->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'root attribute' => [
                'attribute' => new Attribute(new ClassName('Attribute')),
                'expected' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([new ClassName('Attribute')]),
                    ),
                ]),
            ],
            'non-root attribute' => [
                'attribute' => new Attribute(new ClassName(self::class)),
                'expected' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([new ClassName(self::class)]),
                    ),
                ]),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(Attribute $attribute, string $expectedRenderedAttribute): void
    {
        $this->assertRenderResolvable($expectedRenderedAttribute, $attribute);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'root attribute, no arguments' => [
                'attribute' => new Attribute(new ClassName('Attribute')),
                'expectedRenderedAttribute' => '#[Attribute]',
            ],
            'non-root attribute, no arguments' => [
                'attribute' => new Attribute(new ClassName(self::class)),
                'expectedRenderedAttribute' => '#[AttributeTest]',
            ],
            'root attribute, has arguments' => [
                'attribute' => new Attribute(new ClassName('Attribute'), ['\'one\'', '\'two\'', '\'three\'']),
                'expectedRenderedAttribute' => "#[Attribute('one', 'two', 'three')]",
            ],
            'non-root attribute, has arguments' => [
                'attribute' => new Attribute(new ClassName('Attribute'), ['\'one\'', '\'two\'', '\'three\'']),
                'expectedRenderedAttribute' => "#[Attribute('one', 'two', 'three')]",
            ],
        ];
    }
}
