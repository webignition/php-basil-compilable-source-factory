<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassSignature;

class ClassSignatureTest extends AbstractResolvableTestCase
{
    public function testGetName(): void
    {
        $name = 'ClassName';
        $signature = new ClassSignature($name);

        self::assertSame($name, $signature->getName());
    }

    /**
     * @dataProvider getBaseClassDataProvider
     */
    public function testGetBaseClass(ClassSignature $signature, ?ClassName $expectedBaseClass): void
    {
        self::assertSame($expectedBaseClass, $signature->getBaseClass());
    }

    /**
     * @return array<mixed>
     */
    public function getBaseClassDataProvider(): array
    {
        $baseClass = new ClassName(TestCase::class);

        return [
            'no base class' => [
                'signature' => new ClassSignature('ClassName'),
                'expectedBaseClass' => null,
            ],
            'has base class' => [
                'signature' => new ClassSignature('ClassName', $baseClass),
                'expectedBaseClass' => $baseClass,
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ClassSignature $classSignature, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $classSignature);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'no base class' => [
                'classSignature' => new ClassSignature('NameOfClass'),
                'expectedString' => 'class NameOfClass',
            ],
            'base class in root namespace' => [
                'classSignature' => new ClassSignature('NameOfClass', new ClassName('TestCase')),
                'expectedString' => 'class NameOfClass extends \TestCase',
            ],
            'base class in non-root namespace' => [
                'classSignature' => new ClassSignature('NameOfClass', new ClassName(TestCase::class)),
                'expectedString' => 'class NameOfClass extends TestCase',
            ],
        ];
    }
}
