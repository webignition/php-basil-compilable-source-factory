<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Attribute\Attribute;
use webignition\BasilCompilableSourceFactory\Model\Attribute\DataProviderAttribute;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class DataProviderAttributeTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(DataProviderAttribute $attribute, Metadata $expected): void
    {
        self::assertEquals($expected, $attribute->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'default' => [
                'attribute' => new DataProviderAttribute('dataProviderMethodName'),
                'expected' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([new ClassName(DataProvider::class)]),
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
            'default' => [
                'attribute' => new DataProviderAttribute('dataProviderMethodName'),
                'expectedRenderedAttribute' => "#[DataProvider('dataProviderMethodName')]",
            ],
        ];
    }
}
