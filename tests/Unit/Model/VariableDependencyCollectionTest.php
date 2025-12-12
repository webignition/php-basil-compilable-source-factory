<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;

class VariableDependencyCollectionTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param non-empty-string[]   $names
     * @param VariableDependency[] $expectedPlaceholders
     */
    public function testCreate(array $names, array $expectedPlaceholders): void
    {
        $collection = new VariableDependencyCollection($names);

        $this->assertCount(count($expectedPlaceholders), $collection);
        $this->assertEquals($expectedPlaceholders, $this->getCollectionVariablePlaceholders($collection));
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'default' => [
                'names' => [
                    'DEPENDENCY_1',
                    'DEPENDENCY_2',
                    'DEPENDENCY_2',
                    'DEPENDENCY_3',
                ],
                'expectedPlaceholders' => [
                    'DEPENDENCY_1' => new VariableDependency('DEPENDENCY_1'),
                    'DEPENDENCY_2' => new VariableDependency('DEPENDENCY_2'),
                    'DEPENDENCY_3' => new VariableDependency('DEPENDENCY_3'),
                ],
            ],
        ];
    }

    public function testMerge(): void
    {
        $collection = new VariableDependencyCollection(['ONE']);

        $collection = $collection->merge(new VariableDependencyCollection(['TWO', 'THREE']));
        $collection = $collection->merge(
            new VariableDependencyCollection(['THREE', 'FOUR'])
        );

        $this->assertCount(4, $collection);

        $this->assertEquals(
            [
                'ONE' => new VariableDependency('ONE'),
                'TWO' => new VariableDependency('TWO'),
                'THREE' => new VariableDependency('THREE'),
                'FOUR' => new VariableDependency('FOUR'),
            ],
            $this->getCollectionVariablePlaceholders($collection)
        );
    }

    public function testIterator(): void
    {
        $collectionValues = [
            'ONE' => 'ONE',
            'TWO' => 'TWO',
            'THREE' => 'THREE',
        ];

        $collection = new VariableDependencyCollection(array_values($collectionValues));

        foreach ($collection as $id => $variablePlaceholder) {
            $expectedPlaceholder = new VariableDependency($collectionValues[$id]);

            $this->assertEquals($expectedPlaceholder, $variablePlaceholder);
        }
    }

    /**
     * @return array<mixed>
     */
    private function getCollectionVariablePlaceholders(VariableDependencyCollection $collection): array
    {
        $reflectionObject = new \ReflectionObject($collection);
        $property = $reflectionObject->getProperty('dependencies');
        $property->setAccessible(true);

        $value = $property->getValue($collection);

        return is_array($value) ? $value : [];
    }
}
