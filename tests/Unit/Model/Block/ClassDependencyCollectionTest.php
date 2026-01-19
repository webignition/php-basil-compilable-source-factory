<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\ClassNameTest;

class ClassDependencyCollectionTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(ClassNameCollection $classNames, ClassNameCollection $expectedClassNames): void
    {
        $collection = new ClassDependencyCollection($classNames);

        $this->assertEquals($expectedClassNames, $collection->getClassNames());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'classNames' => new ClassNameCollection([]),
                'expectedClassNames' => new ClassNameCollection([]),
            ],
            'non-empty, duplicates are removed' => [
                'classNames' => new ClassNameCollection([
                    new ClassName(EmptyLine::class),
                    new ClassName(SingleLineComment::class),
                    new ClassName(EmptyLine::class),
                ]),
                'expectedClassNames' => new ClassNameCollection([
                    new ClassName(EmptyLine::class),
                    new ClassName(SingleLineComment::class),
                ]),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(ClassDependencyCollection $collection, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $collection);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'non-empty' => [
                'collection' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                        new ClassName(ClassNameTest::class, 'BaseTest'),
                    ])
                ),
                'expectedString' => 'use webignition\BasilCompilableSourceFactory\Model\ClassName;' . "\n"
                    . 'use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\ClassNameTest as BaseTest;',
            ],
            'lines are sorted' => [
                'collection' => new ClassDependencyCollection(
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
            'one' => [
                'collection' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                    ])
                ),
                'expectedCount' => 1,
            ],
            'two' => [
                'collection' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                        new ClassName('Acme\B'),
                    ])
                ),
                'expectedCount' => 2,
            ],
            'three' => [
                'collection' => new ClassDependencyCollection(
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
            'not empty' => [
                'collection' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName('Acme\A'),
                    ])
                ),
                'expectedIsEmpty' => false,
            ],
        ];
    }
}
