<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\LineInterface;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
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
        $expectedClassNames = $this->getClassDependencyNames($expected);
        $actualClassNames = $this->getClassDependencyNames($actual);

        $this->assertSame($expectedClassNames, $actualClassNames);
    }

    private function getClassDependencyNames(ClassDependencyCollection $classDependencyCollection)
    {
        $names = [];

        foreach ($classDependencyCollection->getLines() as $classDependency) {
            $names[] = $classDependency->getContent();
        }

        sort($names);

        return $names;
    }

    private function assertVariablePlaceholderCollection(
        VariablePlaceholderCollection $expected,
        VariablePlaceholderCollection $actual,
        string $collectionName
    ) {
        $expectedPlaceholderNames = $this->getVariablePlaceholderNames($expected);
        $actualPlaceholderNames = $this->getVariablePlaceholderNames($actual);

        $message = $collectionName . ' are not equal';

        $this->assertSame($expectedPlaceholderNames, $actualPlaceholderNames, $message);
    }

    private function getVariablePlaceholderNames(VariablePlaceholderCollection $variablePlaceholderCollection)
    {
        $names = [];

        foreach ($variablePlaceholderCollection as $variablePlaceholder) {
            $names[] = $variablePlaceholder->getName();
        }

        sort($names);

        return $names;
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
