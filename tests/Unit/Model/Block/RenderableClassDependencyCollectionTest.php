<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Block\RenderableClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\ClassNameTest;

class RenderableClassDependencyCollectionTest extends AbstractResolvableTestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRender(RenderableClassDependencyCollection $collection, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $collection);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new RenderableClassDependencyCollection(new ClassNameCollection([])),
                'expectedString' => '',
            ],
            'non-empty' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                        new ClassName(ClassNameTest::class, 'BaseTest'),
                    ])
                ),
                'expectedString' => 'use webignition\BasilCompilableSourceFactory\Model\ClassName;' . "\n"
                    . 'use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\ClassNameTest as BaseTest;',
            ],
            'lines are sorted' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\C'),
                        new ClassName('Acme\A'),
                        new ClassName('Acme\B'),
                    ])
                ),
                'expectedString' => 'use Acme\A;' . "\n"
                    . 'use Acme\B;' . "\n"
                    . 'use Acme\C;',
            ],
            'single item in root namespace' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(\Throwable::class),
                    ])
                ),
                'expectedString' => '',
            ],
            'single item, with alias, in root namespace' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(\Throwable::class, 'Bouncy'),
                    ])
                ),
                'expectedString' => 'use Throwable as Bouncy;',
            ],
            'items in root namespace and not in root namespace' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                        new ClassName('B'),
                        new ClassName('Acme\C'),
                    ])
                ),
                'expectedString' => 'use Acme\A;' . "\n"
                    . 'use Acme\C;',
            ],
        ];
    }

    #[DataProvider('countDataProvider')]
    public function testCount(ClassDependencyCollection $collection, int $expectedCount): void
    {
        self::assertCount($expectedCount, $collection);
    }

    #[DataProvider('countDataProvider')]
    public function testCountable(ClassDependencyCollection $collection, int $expectedCount): void
    {
        self::assertCount($expectedCount, $collection);
    }

    /**
     * @return array<mixed>
     */
    public static function countDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new RenderableClassDependencyCollection(new ClassNameCollection([])),
                'expectedCount' => 0,
            ],
            'one' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                    ])
                ),
                'expectedCount' => 1,
            ],
            'two' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                        new ClassName('Acme\B'),
                    ])
                ),
                'expectedCount' => 2,
            ],
            'three' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                        new ClassName('Acme\B'),
                        new ClassName('Acme\C'),
                    ])
                ),
                'expectedCount' => 3,
            ],
        ];
    }

    #[DataProvider('isEmptyDataProvider')]
    public function testIsEmpty(ClassDependencyCollection $collection, bool $expectedIsEmpty): void
    {
        self::assertSame($expectedIsEmpty, $collection->isEmpty());
    }

    /**
     * @return array<mixed>
     */
    public static function isEmptyDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new RenderableClassDependencyCollection(new ClassNameCollection([])),
                'expectedIsEmpty' => true,
            ],
            'not empty' => [
                'collection' => new RenderableClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                    ])
                ),
                'expectedIsEmpty' => false,
            ],
        ];
    }
}
