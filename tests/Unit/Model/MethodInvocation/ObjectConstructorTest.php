<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectConstructor;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectConstructorTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(
        ClassName $class,
        MethodArgumentsInterface $arguments,
        MetadataInterface $expectedMetadata
    ): void {
        $constructor = new ObjectConstructor(
            class: $class,
            arguments: $arguments,
            mightThrow: false,
        );

        $this->assertSame($class->getClass(), $constructor->getCall());
        $this->assertEquals($expectedMetadata, $constructor->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'no arguments' => [
                'class' => new ClassName(ObjectConstructor::class),
                'arguments' => new MethodArguments(),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ObjectConstructor::class,
                    ],
                ),
            ],
            'single argument' => [
                'class' => new ClassName(ObjectConstructor::class),
                'arguments' => new MethodArguments([
                    new LiteralExpression('1'),
                ]),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ObjectConstructor::class,
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(ObjectConstructor $constructor, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $constructor);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        $classDependency = new ClassName('Acme\Model');

        return [
            'no arguments' => [
                'constructor' => new ObjectConstructor(
                    class: $classDependency,
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => 'new Model()',
            ],
            'no arguments, class in root namespace' => [
                'constructor' => new ObjectConstructor(
                    class: new ClassName(\Exception::class),
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedString' => 'new \Exception()',
            ],
            'has arguments, inline' => [
                'constructor' => new ObjectConstructor(
                    class: $classDependency,
                    arguments: new MethodArguments([
                        new LiteralExpression('1'),
                        new LiteralExpression("\\'single-quoted value\\'"),
                    ]),
                    mightThrow: false,
                ),
                'expectedString' => "new Model(1, \\'single-quoted value\\')",
            ],
            'has arguments, stacked' => [
                'constructor' => new ObjectConstructor(
                    class: $classDependency,
                    arguments: new MethodArguments(
                        [
                            new LiteralExpression('1'),
                            new LiteralExpression("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED
                    ),
                    mightThrow: false,
                ),
                'expectedString' => "new Model(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\',\n"
                    . ')',
            ],
        ];
    }
}
