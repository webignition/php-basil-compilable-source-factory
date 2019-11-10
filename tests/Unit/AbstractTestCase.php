<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\LineInterface;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertBlockContentEquals(BlockInterface $expected, BlockInterface $actual)
    {
        $this->assertBlockLines($expected, $actual);
    }

    private function assertBlockLines(BlockInterface $expected, BlockInterface $actual)
    {
        $expectedLines = $expected->getLines();
        $actualLines = $actual->getLines();

        $expectedLineContent = [];
        foreach ($expectedLines as $expectedLine) {
            $expectedLineContent[] = $expectedLine->getContent();
        }

        $expectedLineContent = $this->getSourceLineContent($expectedLines);
        $actualLineContent = $this->getSourceLineContent($actualLines);

        $this->assertSame($expectedLineContent, $actualLineContent);

        foreach ($expectedLines as $lineIndex => $expectedLine) {
            $actualLine = $actualLines[$lineIndex];

            $this->assertEquals(get_class($expectedLine), get_class($actualLine));
            $this->assertEquals($expectedLine->getContent(), $actualLine->getContent());
        }
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
