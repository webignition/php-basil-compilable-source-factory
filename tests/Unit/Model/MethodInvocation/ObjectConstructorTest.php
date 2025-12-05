<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectConstructorTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        ClassName $class,
        ?MethodArgumentsInterface $arguments,
        MetadataInterface $expectedMetadata
    ): void {
        $constructor = new ObjectConstructor($class, $arguments);

        $this->assertSame($class->getClass(), $constructor->getCall());
        $this->assertSame($arguments, $constructor->getArguments());
        $this->assertEquals($expectedMetadata, $constructor->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no arguments' => [
                'class' => new ClassName(ObjectConstructor::class),
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ObjectConstructor::class)
                        ])
                    ),
                ]),
            ],
            'single argument' => [
                'class' => new ClassName(ObjectConstructor::class),
                'arguments' => new MethodArguments([
                    new LiteralExpression('1'),
                ]),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ObjectConstructor::class)
                        ])
                    ),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ObjectConstructor $constructor, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $constructor);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        $classDependency = new ClassName('Acme\Model');

        return [
            'no arguments' => [
                'constructor' => new ObjectConstructor($classDependency),
                'expectedString' => 'new Model()',
            ],
            'no arguments, class in root namespace' => [
                'constructor' => new ObjectConstructor(
                    new ClassName(\Exception::class)
                ),
                'expectedString' => 'new \Exception()',
            ],
            'has arguments, inline' => [
                'constructor' => new ObjectConstructor(
                    $classDependency,
                    new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ])
                ),
                'expectedString' => "new Model(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'constructor' => new ObjectConstructor(
                    $classDependency,
                    new MethodArguments(
                        [
                            new LiteralExpression('1'),
                            new LiteralExpression("\\'single-quoted value\\'"),
                        ],
                        MethodArguments::FORMAT_STACKED
                    )
                ),
                'expectedString' => "new Model(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\'\n"
                    . ')',
            ],
        ];
    }
}
