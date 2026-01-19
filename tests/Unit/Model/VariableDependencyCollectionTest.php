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
     * @param VariableName[]       $names
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
                    VariableName::DOM_CRAWLER_NAVIGATOR,
                    VariableName::PANTHER_CRAWLER,
                    VariableName::PHPUNIT_TEST_CASE,
                    VariableName::PHPUNIT_TEST_CASE,
                ],
                'expectedPlaceholders' => [
                    'NAVIGATOR' => new VariableDependency(VariableName::DOM_CRAWLER_NAVIGATOR),
                    'CRAWLER' => new VariableDependency(VariableName::PANTHER_CRAWLER),
                    'PHPUNIT' => new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
                ],
            ],
        ];
    }

    public function testMerge(): void
    {
        $collection = new VariableDependencyCollection([VariableName::PANTHER_CLIENT]);

        $collection = $collection->merge(new VariableDependencyCollection([
            VariableName::PANTHER_CRAWLER,
            VariableName::ENVIRONMENT_VARIABLE_ARRAY,
        ]));
        $collection = $collection->merge(
            new VariableDependencyCollection([
                VariableName::ENVIRONMENT_VARIABLE_ARRAY,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ])
        );

        $this->assertCount(4, $collection);

        $this->assertEquals(
            [
                'CLIENT' => new VariableDependency(VariableName::PANTHER_CLIENT),
                'CRAWLER' => new VariableDependency(VariableName::PANTHER_CRAWLER),
                'ENV' => new VariableDependency(VariableName::ENVIRONMENT_VARIABLE_ARRAY),
                'NAVIGATOR' => new VariableDependency(VariableName::DOM_CRAWLER_NAVIGATOR),
            ],
            $this->getCollectionVariablePlaceholders($collection)
        );
    }

    public function testIterator(): void
    {
        $collectionValues = [
            'CRAWLER' => VariableName::PANTHER_CRAWLER,
            'ENV' => VariableName::ENVIRONMENT_VARIABLE_ARRAY,
            'NAVIGATOR' => VariableName::DOM_CRAWLER_NAVIGATOR,
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
