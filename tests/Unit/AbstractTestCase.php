<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Block\BlockInterface;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodDefinitionInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertMethodEquals(MethodDefinitionInterface $expected, MethodDefinitionInterface $actual)
    {
        $this->assertSame($expected->getName(), $actual->getName());
        $this->assertEquals($expected->getDocBlock(), $actual->getDocBlock());
        $this->assertSame($expected->getReturnType(), $actual->getReturnType());
        $this->assertSame($expected->isStatic(), $actual->isStatic());
        $this->assertSame($expected->getArguments(), $actual->getArguments());
        $this->assertBlockContentEquals($expected, $actual);
    }

    protected function assertBlockContentEquals(BlockInterface $expected, BlockInterface $actual)
    {
        $this->assertSame($expected->render(), $actual->render());
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

    /**
     * @param ClassDependencyCollection $classDependencyCollection
     *
     * @return array<string>
     */
    private function getClassDependencyNames(ClassDependencyCollection $classDependencyCollection): array
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

    /**
     * @param VariablePlaceholderCollection $variablePlaceholderCollection
     *
     * @return array<string>
     */
    private function getVariablePlaceholderNames(VariablePlaceholderCollection $variablePlaceholderCollection)
    {
        $names = [];

        foreach ($variablePlaceholderCollection as $variablePlaceholder) {
            $names[] = $variablePlaceholder->getName();
        }

        sort($names);

        return $names;
    }
}
