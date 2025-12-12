<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Attribute\Attribute;
use webignition\BasilCompilableSourceFactory\Model\Attribute\AttributeCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CollectionTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(AttributeCollection $collection, Metadata $expected): void
    {
        self::assertEquals($expected, $collection->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'empty collection' => [
                'collection' => new AttributeCollection(),
                'expected' => Metadata::create(),
            ],
            'single item' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName(self::class))),
                'expected' => Metadata::create(
                    classNames: [
                        self::class,
                    ],
                ),
            ],
            'two items' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName(self::class)))
                    ->add(new Attribute(new ClassName(AttributeCollection::class))),
                'expected' => Metadata::create(
                    classNames: [
                        self::class,
                        AttributeCollection::class,
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(AttributeCollection $collection, string $expectedRenderedCollection): void
    {
        $this->assertRenderResolvable($expectedRenderedCollection, $collection);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'single root attribute, no arguments' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName('Attribute'))),
                'expectedRenderedCollection' => '#[Attribute]',
            ],
            'two root attributes, no arguments' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName('Attribute1')))
                    ->add(new Attribute(new ClassName('Attribute2'))),
                'expectedRenderedCollection' => <<<'EOD'
                    #[Attribute1]
                    #[Attribute2]
                    EOD,
            ],
            'single root attribute, has arguments' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName('Attribute'), ['\'one\'', '\'two\'', '\'three\''])),
                'expectedRenderedCollection' => "#[Attribute('one', 'two', 'three')]",
            ],
            'two root attributes, has arguments' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName('Attribute1'), ['\'one\'', '\'two\'']))
                    ->add(new Attribute(new ClassName('Attribute2'), ['\'three\''])),
                'expectedRenderedCollection' => <<<'EOD'
                    #[Attribute1('one', 'two')]
                    #[Attribute2('three')]
                    EOD,
            ],
            'mixed root/non-root attributes, has/has not arguments' => [
                'collection' => new AttributeCollection()
                    ->add(new Attribute(new ClassName('Attribute1'), ['\'one\'', '\'two\'']))
                    ->add(new Attribute(new ClassName(self::class)))
                    ->add(new Attribute(new ClassName('Attribute3'), ['\'three\'']))
                    ->add(new Attribute(new ClassName(AttributeCollection::class))),
                'expectedRenderedCollection' => <<<'EOD'
                    #[Attribute1('one', 'two')]
                    #[CollectionTest]
                    #[Attribute3('three')]
                    #[AttributeCollection]
                    EOD,
            ],
        ];
    }
}
