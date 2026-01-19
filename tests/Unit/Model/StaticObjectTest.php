<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;

class StaticObjectTest extends TestCase
{
    /**
     * @param non-empty-string $object
     */
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(string $object, MetadataInterface $expectedMetadata): void
    {
        $staticObject = new StaticObject($object);

        $this->assertEquals($expectedMetadata, $staticObject->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'string reference' => [
                'object' => 'parent',
                'expectedMetadata' => new Metadata(),
            ],
            'global classname' => [
                'object' => \stdClass::class,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        \stdClass::class,
                    ],
                ),
            ],
            'namespaced classname' => [
                'object' => ClassName::class,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClassName::class,
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('toStringDataProvider')]
    public function testToString(StaticObject $object, string $expectedString): void
    {
        $this->assertSame($expectedString, (string) $object);
    }

    /**
     * @return array<mixed>
     */
    public static function toStringDataProvider(): array
    {
        return [
            'string reference' => [
                'object' => new StaticObject('parent'),
                'expectedString' => 'parent',
            ],
            'root-namespaced class' => [
                'object' => new StaticObject(\stdClass::class),
                'expectedString' => '\stdClass',
            ],
            'namespaced class' => [
                'object' => new StaticObject(ClassName::class),
                'expectedString' => 'ClassName',
            ],
        ];
    }
}
