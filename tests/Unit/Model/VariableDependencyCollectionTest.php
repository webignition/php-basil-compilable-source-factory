<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
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
                    VariableName::DOM_CRAWLER_NAVIGATOR->value,
                    VariableName::PANTHER_CRAWLER->value,
                    VariableName::PHPUNIT_TEST_CASE->value,
                    VariableName::PHPUNIT_TEST_CASE->value,
                ],
                'expectedPlaceholders' => [
                    'NAVIGATOR' => new VariableDependency(VariableName::DOM_CRAWLER_NAVIGATOR->value),
                    'CRAWLER' => new VariableDependency(VariableName::PANTHER_CRAWLER->value),
                    'PHPUNIT' => new VariableDependency(VariableName::PHPUNIT_TEST_CASE->value),
                ],
            ],
        ];
    }

    public function testMerge(): void
    {
        $collection = new VariableDependencyCollection([VariableName::PANTHER_CLIENT->value]);

        $collection = $collection->merge(new VariableDependencyCollection([
            VariableName::PANTHER_CRAWLER->value,
            VariableName::ENVIRONMENT_VARIABLE_ARRAY->value,
        ]));
        $collection = $collection->merge(
            new VariableDependencyCollection([
                VariableName::ENVIRONMENT_VARIABLE_ARRAY->value,
                VariableName::DOM_CRAWLER_NAVIGATOR->value,
            ])
        );

        $this->assertCount(4, $collection);

        $this->assertEquals(
            [
                'CLIENT' => new VariableDependency(VariableName::PANTHER_CLIENT->value),
                'CRAWLER' => new VariableDependency(VariableName::PANTHER_CRAWLER->value),
                'ENV' => new VariableDependency(VariableName::ENVIRONMENT_VARIABLE_ARRAY->value),
                'NAVIGATOR' => new VariableDependency(VariableName::DOM_CRAWLER_NAVIGATOR->value),
            ],
            $this->getCollectionVariablePlaceholders($collection)
        );
    }

    public function testIterator(): void
    {
        $collectionValues = [
            'CRAWLER' => VariableName::PANTHER_CRAWLER->value,
            'ENV' => VariableName::ENVIRONMENT_VARIABLE_ARRAY->value,
            'NAVIGATOR' => VariableName::DOM_CRAWLER_NAVIGATOR->value,
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
