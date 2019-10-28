<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilationSource\AbstractUniqueCollection;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\UniqueItemInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertJsonSerializedData(array $expectedSerializedData, \JsonSerializable $object)
    {
        $this->assertSame($expectedSerializedData, $object->jsonSerialize());
    }

    protected function assertMetadataEquals(MetadataInterface $expected, MetadataInterface $actual)
    {
        $this->assertClassDependencyCollectionEquals(
            $expected->getClassDependencies(),
            $actual->getClassDependencies()
        );

        $this->assertVariablePlaceholderCollection(
            $expected->getVariableDependencies(),
            $actual->getVariableDependencies(),
            'Variable dependencies'
        );

        $this->assertVariablePlaceholderCollection(
            $expected->getVariableExports(),
            $actual->getVariableExports(),
            'Variable exports'
        );
    }

    private function assertClassDependencyCollectionEquals(
        ClassDependencyCollection $expected,
        ClassDependencyCollection $actual
    ) {
        $expectedClassNames = $this->getCollectionIds($expected);
        $actualClassNames = $this->getCollectionIds($actual);

        $this->assertSame($expectedClassNames, $actualClassNames);
    }

    private function assertVariablePlaceholderCollection(
        VariablePlaceholderCollection $expected,
        VariablePlaceholderCollection $actual,
        string $collectionName
    ) {
        $expectedPlaceholderNames = $this->getCollectionIds($expected);
        $actualPlaceholderNames = $this->getCollectionIds($actual);

        $message = $collectionName . ' are not equal';

        $this->assertSame($expectedPlaceholderNames, $actualPlaceholderNames, $message);
    }

    private function getCollectionIds(AbstractUniqueCollection $collection)
    {
        $ids = array_map(
            function (UniqueItemInterface $placeholder) {
                return $placeholder->getId();
            },
            $collection->getAll()
        );

        sort($ids);

        return $ids;
    }
}
