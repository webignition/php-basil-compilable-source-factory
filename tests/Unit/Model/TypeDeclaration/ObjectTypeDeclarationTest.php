<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\TypeDeclaration;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\TypeDeclarationInterface;

class ObjectTypeDeclarationTest extends TestCase
{
    public function testGetMetadata(): void
    {
        $type = new ClassName(\Exception::class);
        $declaration = new ObjectTypeDeclaration($type);

        $expectedMetadata = new Metadata(
            classNames: [$type->getClassName()],
        );

        $this->assertEquals($expectedMetadata, $declaration->getMetadata());
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(ObjectTypeDeclaration $declaration, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $declaration);
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        return [
            'class in root namespace' => [
                'declaration' => new ObjectTypeDeclaration(
                    new ClassName(\Exception::class)
                ),
                'expectedString' => '\Exception',
            ],
            'interface in root namespace' => [
                'declaration' => new ObjectTypeDeclaration(
                    new ClassName(\Traversable::class)
                ),
                'expectedString' => '\Traversable',
            ],
            'class not in root namespace' => [
                'declaration' => new ObjectTypeDeclaration(
                    new ClassName(ObjectTypeDeclaration::class)
                ),
                'expectedString' => 'ObjectTypeDeclaration',
            ],
            'interface not in root namespace' => [
                'declaration' => new ObjectTypeDeclaration(
                    new ClassName(TypeDeclarationInterface::class)
                ),
                'expectedString' => 'TypeDeclarationInterface',
            ],
            'class not in root namespace, has alias' => [
                'declaration' => new ObjectTypeDeclaration(
                    new ClassName(ObjectTypeDeclaration::class, 'AliasName')
                ),
                'expectedString' => 'AliasName',
            ],
        ];
    }
}
