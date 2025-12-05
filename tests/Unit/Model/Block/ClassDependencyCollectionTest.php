<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\ClassNameTest;
use webignition\ObjectReflector\ObjectReflector;

class ClassDependencyCollectionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param ClassName[] $dependencies
     * @param ClassName[] $expectedDependencies
     */
    public function testCreate(array $dependencies, array $expectedDependencies): void
    {
        $collection = new ClassDependencyCollection($dependencies);

        $this->assertEquals($expectedDependencies, ObjectReflector::getProperty($collection, 'classNames'));
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'empty' => [
                'classNames' => [],
                'expectedClassNames' => [],
            ],
            'no class dependency lines' => [
                'classNames' => [
                    new EmptyLine(),
                    new SingleLineComment(''),
                ],
                'expectedClassNames' => [],
            ],
            'has class dependency lines' => [
                'classNames' => [
                    new EmptyLine(),
                    new SingleLineComment(''),
                    new ClassName(EmptyLine::class),
                    new ClassName(SingleLineComment::class),
                    new ClassName(EmptyLine::class),
                ],
                'expectedClassNames' => [
                    new ClassName(EmptyLine::class),
                    new ClassName(SingleLineComment::class),
                ],
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ClassDependencyCollection $collection, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $collection);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new ClassDependencyCollection([]),
                'expectedString' => '',
            ],
            'non-empty' => [
                'collection' => new ClassDependencyCollection([
                    new ClassName(ClassName::class),
                    new ClassName(ClassNameTest::class, 'BaseTest'),
                ]),
                'expectedString' => 'use webignition\BasilCompilableSourceFactory\Model\ClassName;' . "\n" .
                    'use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\ClassNameTest as BaseTest;',
            ],
            'lines are sorted' => [
                'collection' => new ClassDependencyCollection([
                    new ClassName('Acme\C'),
                    new ClassName('Acme\A'),
                    new ClassName('Acme\B'),
                ]),
                'expectedString' => 'use Acme\A;' . "\n" .
                    'use Acme\B;' . "\n" .
                    'use Acme\C;',
            ],
        ];
    }

    /**
     * @dataProvider countDataProvider
     */
    public function testCount(ClassDependencyCollection $collection, int $expectedCount): void
    {
        self::assertCount($expectedCount, $collection);
    }

    /**
     * @dataProvider countDataProvider
     */
    public function testCountable(ClassDependencyCollection $collection, int $expectedCount): void
    {
        self::assertCount($expectedCount, $collection);
    }

    /**
     * @return array<mixed>
     */
    public function countDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new ClassDependencyCollection(),
                'expectedCount' => 0,
            ],
            'one' => [
                'collection' => new ClassDependencyCollection([
                    new ClassName('Acme\A'),
                ]),
                'expectedCount' => 1,
            ],
            'two' => [
                'collection' => new ClassDependencyCollection([
                    new ClassName('Acme\A'),
                    new ClassName('Acme\B'),
                ]),
                'expectedCount' => 2,
            ],
            'three' => [
                'collection' => new ClassDependencyCollection([
                    new ClassName('Acme\A'),
                    new ClassName('Acme\B'),
                    new ClassName('Acme\C'),
                ]),
                'expectedCount' => 3,
            ],
        ];
    }

    /**
     * @dataProvider isEmptyDataProvider
     */
    public function testIsEmpty(ClassDependencyCollection $collection, bool $expectedIsEmpty): void
    {
        self::assertSame($expectedIsEmpty, $collection->isEmpty());
    }

    /**
     * @return array<mixed>
     */
    public function isEmptyDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new ClassDependencyCollection(),
                'expectedIsEmpty' => true,
            ],
            'not empty' => [
                'collection' => new ClassDependencyCollection([
                    new ClassName('Acme\A'),
                ]),
                'expectedIsEmpty' => false,
            ],
        ];
    }
}
