<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;

class VariableDependencyCollectionTest extends TestCase
{
    /**
     * @param DependencyName[] $names
     * @param Property[]       $expectedPlaceholders
     */
    #[DataProvider('createDataProvider')]
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
                    DependencyName::DOM_CRAWLER_NAVIGATOR,
                    DependencyName::PANTHER_CRAWLER,
                    DependencyName::PHPUNIT_TEST_CASE,
                    DependencyName::PHPUNIT_TEST_CASE,
                ],
                'expectedPlaceholders' => [
                    'NAVIGATOR' => Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR),
                    'CRAWLER' => Property::asDependency(DependencyName::PANTHER_CRAWLER),
                    'PHPUNIT' => Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
                ],
            ],
        ];
    }

    public function testMerge(): void
    {
        $collection = new VariableDependencyCollection([DependencyName::PANTHER_CLIENT]);

        $collection = $collection->merge(new VariableDependencyCollection([
            DependencyName::PANTHER_CRAWLER,
            DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
        ]));
        $collection = $collection->merge(
            new VariableDependencyCollection([
                DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                DependencyName::DOM_CRAWLER_NAVIGATOR,
            ])
        );

        $this->assertCount(4, $collection);

        $this->assertEquals(
            [
                'CLIENT' => Property::asDependency(DependencyName::PANTHER_CLIENT),
                'CRAWLER' => Property::asDependency(DependencyName::PANTHER_CRAWLER),
                'ENV' => Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
                'NAVIGATOR' => Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR),
            ],
            $this->getCollectionVariablePlaceholders($collection)
        );
    }

    public function testIterator(): void
    {
        $collectionValues = [
            'CRAWLER' => DependencyName::PANTHER_CRAWLER,
            'ENV' => DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
            'NAVIGATOR' => DependencyName::DOM_CRAWLER_NAVIGATOR,
        ];

        $collection = new VariableDependencyCollection(array_values($collectionValues));

        foreach ($collection as $id => $variablePlaceholder) {
            $expectedPlaceholder = Property::asDependency($collectionValues[$id]);

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
