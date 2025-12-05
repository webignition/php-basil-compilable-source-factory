<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\ObjectReflector\ObjectReflector;

class ClassNameTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $className, ?string $alias): void
    {
        $classDependency = new ClassName($className, $alias);

        $this->assertSame($className, $classDependency->getClassName());
        $this->assertSame($alias, ObjectReflector::getProperty($classDependency, 'alias'));
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no alias' => [
                'className' => ClassNameTest::class,
                'alias' => null,
            ],
            'has alias' => [
                'className' => TestCase::class,
                'alias' => 'ClassNameAlias',
            ],
        ];
    }

    /**
     * @dataProvider getClassDataProvider
     */
    public function testGetClass(ClassName $classDependency, string $expectedClass): void
    {
        $this->assertSame($expectedClass, $classDependency->getClass());
    }

    /**
     * @return array<mixed>
     */
    public function getClassDataProvider(): array
    {
        return [
            'global namespace' => [
                'className' => new ClassName('Global'),
                'expectedClass' => 'Global',
            ],
            'namespaced' => [
                'className' => new ClassName(ClassName::class),
                'expectedClass' => 'ClassName',
            ],
        ];
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(ClassName $classDependency, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $classDependency);
    }

    /**
     * @return array<mixed>
     */
    public function toStringDataProvider(): array
    {
        return [
            'no alias' => [
                'className' => new ClassName(ClassName::class),
                'expectedString' => 'ClassName',
            ],
            'has alias' => [
                'className' => new ClassName(ClassNameTest::class, 'BaseTest'),
                'expectedString' => 'BaseTest',
            ],
            'no alias, in root namespace' => [
                'className' => new ClassName(\Throwable::class),
                'expectedString' => 'Throwable',
            ],
            'has alias, in root namespace' => [
                'className' => new ClassName(\Throwable::class, 'Bouncy'),
                'expectedString' => 'Bouncy',
            ],
        ];
    }

    /**
     * @dataProvider isInRootNamespaceDataProvider
     */
    public function testIsInRootNamespace(ClassName $classDependency, bool $expectedIsInRootNamespace): void
    {
        $this->assertSame($expectedIsInRootNamespace, $classDependency->isInRootNamespace());
    }

    /**
     * @return array<mixed>
     */
    public function isInRootNamespaceDataProvider(): array
    {
        return [
            'not in root namespace, no alias' => [
                'className' => new ClassName(ClassName::class),
                'expectedIsInRootNamespace' => false,
            ],
            'not in root namespace, has alias' => [
                'className' => new ClassName(ClassNameTest::class, 'BaseTest'),
                'expectedIsInRootNamespace' => false,
            ],
            'is in root namespace, no alias' => [
                'className' => new ClassName(\Throwable::class),
                'expectedIsInRootNamespace' => true,
            ],
            'is in root namespace, has alias' => [
                'className' => new ClassName(\Throwable::class, 'Bouncy'),
                'expectedIsInRootNamespace' => true,
            ],
        ];
    }

    /**
     * @dataProvider renderClassNameDataProvider
     */
    public function testRenderClassName(ClassName $classDependency, string $expectedString): void
    {
        $this->assertSame($expectedString, $classDependency->renderClassName());
    }

    /**
     * @return array<mixed>
     */
    public function renderClassNameDataProvider(): array
    {
        return [
            'no alias' => [
                'className' => new ClassName(ClassName::class),
                'expectedString' => 'ClassName',
            ],
            'has alias' => [
                'className' => new ClassName(ClassNameTest::class, 'BaseTest'),
                'expectedString' => 'BaseTest',
            ],
            'no alias, in root namespace' => [
                'className' => new ClassName(\Throwable::class),
                'expectedString' => '\Throwable',
            ],
            'has alias, in root namespace' => [
                'className' => new ClassName(\Throwable::class, 'Bouncy'),
                'expectedString' => 'Bouncy',
            ],
        ];
    }

    /**
     * @dataProvider isFullyQualifiedClassNameDataProvider
     */
    public function testIsFullyQualifiedClassName(string $className, bool $expectedIsFullyQualifiedClassName): void
    {
        self::assertSame($expectedIsFullyQualifiedClassName, ClassName::isFullyQualifiedClassName($className));
    }

    /**
     * @return array<mixed>
     */
    public function isFullyQualifiedClassNameDataProvider(): array
    {
        return [
            'namespaced class name' => [
                'className' => TestCase::class,
                'expectedIsFullyQualifiedClassName' => true,
            ],
            'root-namespaced class name' => [
                'className' => \Throwable::class,
                'expectedIsFullyQualifiedClassName' => true,
            ],
            'self' => [
                'className' => 'self',
                'expectedIsFullyQualifiedClassName' => false,
            ],
            'static' => [
                'className' => 'static',
                'expectedIsFullyQualifiedClassName' => false,
            ],
            'parent' => [
                'className' => 'parent',
                'expectedIsFullyQualifiedClassName' => false,
            ],
        ];
    }
}
