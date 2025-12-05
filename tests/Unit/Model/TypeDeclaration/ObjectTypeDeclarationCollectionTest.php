<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\TypeDeclaration;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectTypeDeclarationCollectionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider getMetadataDataProvider
     */
    public function testGetMetadata(
        ObjectTypeDeclarationCollection $collection,
        MetadataInterface $expectedMetadata
    ): void {
        $this->assertEquals($expectedMetadata, $collection->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function getMetadataDataProvider(): array
    {
        return [
            'empty' => [
                'collection' => new ObjectTypeDeclarationCollection([]),
                'expectedMetadata' => new Metadata(),
            ],
            'non-empty' => [
                'collection' => new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                    new ObjectTypeDeclaration(new ClassName(\Traversable::class)),
                ]),
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(\Exception::class),
                            new ClassName(\Traversable::class),
                        ])
                    ),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ObjectTypeDeclarationCollection $collection, string $expectedString): void
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
                'declaration' => new ObjectTypeDeclarationCollection([]),
                'expectedString' => '',
            ],
            'single, root namespace' => [
                'declaration' => new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                ]),
                'expectedString' => '\Exception',
            ],
            'single, non-root namespace' => [
                'declaration' => new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(TestCase::class)),
                ]),
                'expectedString' => 'TestCase',
            ],
            'single with alias' => [
                'declaration' => new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Exception::class, 'AliasName')),
                ]),
                'expectedString' => 'AliasName',
            ],
            'multiple' => [
                'declaration' => new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                    new ObjectTypeDeclaration(new ClassName(TestCase::class)),
                    new ObjectTypeDeclaration(new ClassName(\Traversable::class)),
                ]),
                'expectedString' => '\Exception | TestCase | \Traversable',
            ],
            'class names are sorted ignoring leading namespace separator' => [
                'declaration' => new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Exception::class, 'Charlie')),
                    new ObjectTypeDeclaration(new ClassName(\Traversable::class, 'Alpha')),
                    new ObjectTypeDeclaration(new ClassName(\Exception::class)),
                    new ObjectTypeDeclaration(new ClassName(ObjectTypeDeclarationCollection::class, 'Bravo')),
                ]),
                'expectedString' => 'Alpha | Bravo | Charlie | \Exception',
            ],
        ];
    }
}
