<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;

class VariableDependencyCollectionTest extends TestCase
{
    /**
     * @param non-empty-string[]   $names
     * @param VariableDependency[] $expectedPlaceholders
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
                    DependencyName::DOM_CRAWLER_NAVIGATOR->value,
                    DependencyName::PANTHER_CRAWLER->value,
                    DependencyName::PHPUNIT_TEST_CASE->value,
                    DependencyName::PHPUNIT_TEST_CASE->value,
                ],
                'expectedPlaceholders' => [
                    'NAVIGATOR' => new VariableDependency(DependencyName::DOM_CRAWLER_NAVIGATOR->value),
                    'CRAWLER' => new VariableDependency(DependencyName::PANTHER_CRAWLER->value),
                    'PHPUNIT' => new VariableDependency(DependencyName::PHPUNIT_TEST_CASE->value),
                ],
            ],
        ];
    }

    public function testMerge(): void
    {
        $collection = new VariableDependencyCollection([DependencyName::PANTHER_CLIENT->value]);

        $collection = $collection->merge(new VariableDependencyCollection([
            DependencyName::PANTHER_CRAWLER->value,
            DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value,
        ]));
        $collection = $collection->merge(
            new VariableDependencyCollection([
                DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value,
                DependencyName::DOM_CRAWLER_NAVIGATOR->value,
            ])
        );

        $this->assertCount(4, $collection);

        $this->assertEquals(
            [
                'CLIENT' => new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                'CRAWLER' => new VariableDependency(DependencyName::PANTHER_CRAWLER->value),
                'ENV' => new VariableDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value),
                'NAVIGATOR' => new VariableDependency(DependencyName::DOM_CRAWLER_NAVIGATOR->value),
            ],
            $this->getCollectionVariablePlaceholders($collection)
        );
    }

    public function testIterator(): void
    {
        $collectionValues = [
            'CRAWLER' => DependencyName::PANTHER_CRAWLER->value,
            'ENV' => DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value,
            'NAVIGATOR' => DependencyName::DOM_CRAWLER_NAVIGATOR->value,
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
