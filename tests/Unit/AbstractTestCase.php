<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilationSource\AbstractUniqueCollection;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineInterface;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\UniqueItemInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertSourceContentEquals(SourceInterface $expected, SourceInterface $actual)
    {
        $this->assertSourceLines($expected, $actual);
    }

    private function assertSourceLines(SourceInterface $expected, SourceInterface $actual)
    {
        $expectedLines = $this->getSourceLines($expected);
        $actualLines = $this->getSourceLines($actual);

        $expectedLineContent = [];
        foreach ($expectedLines as $expectedLine) {
            $expectedLineContent[] = $expectedLine->getContent();
        }

        $expectedLineContent = $this->getSourceLineContent($expectedLines);
        $actualLineContent = $this->getSourceLineContent($actualLines);

        $this->assertSame($expectedLineContent, $actualLineContent);

        foreach ($expectedLines as $lineIndex => $expectedLine) {
            $actualLine = $actualLines[$lineIndex];

            $this->assertSourceClassEquals($expectedLine, $actualLine);
            $this->assertEquals($expectedLine->getContent(), $actualLine->getContent());
        }
    }

    private function assertSourceClassEquals(SourceInterface $expected, SourceInterface $actual)
    {
        $this->assertEquals(get_class($expected), get_class($actual));
    }

    /**
     * @param SourceInterface $source
     *
     * @return LineInterface[]
     */
    private function getSourceLines(SourceInterface $source): array
    {
        $lines = [];

        foreach ($source->getSources() as $innerSource) {
            if ($innerSource instanceof LineInterface) {
                $lines[] = $innerSource;
            } else {
                $lines = array_merge($lines, $this->getSourceLines($innerSource));
            }
        }

        return $lines;
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

    /**
     * @param LineInterface[] $lines
     *
     * @return string[]
     */
    private function getSourceLineContent(array $lines): array
    {
        return array_map(function (LineInterface $line) {
            return $line->getContent();
        }, $lines);
    }
}
