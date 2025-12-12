<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;

class VariableDependencyCollectionTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param VariableNames::*[]   $names
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
                    VariableNames::DOM_CRAWLER_NAVIGATOR,
                    VariableNames::PANTHER_CRAWLER,
                    VariableNames::PHPUNIT_TEST_CASE,
                    VariableNames::PHPUNIT_TEST_CASE,
                ],
                'expectedPlaceholders' => [
                    'NAVIGATOR' => new VariableDependency('NAVIGATOR'),
                    'CRAWLER' => new VariableDependency('CRAWLER'),
                    'PHPUNIT' => new VariableDependency('PHPUNIT'),
                ],
            ],
        ];
    }

    public function testMerge(): void
    {
        $collection = new VariableDependencyCollection([VariableNames::ACTION_FACTORY]);

        $collection = $collection->merge(new VariableDependencyCollection([
            VariableNames::PANTHER_CRAWLER,
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
        ]));
        $collection = $collection->merge(
            new VariableDependencyCollection([
                VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                VariableNames::DOM_CRAWLER_NAVIGATOR,
            ])
        );

        $this->assertCount(4, $collection);

        $this->assertEquals(
            [
                'ACTION_FACTORY' => new VariableDependency(VariableNames::ACTION_FACTORY),
                'CRAWLER' => new VariableDependency(VariableNames::PANTHER_CRAWLER),
                'ENV' => new VariableDependency(VariableNames::ENVIRONMENT_VARIABLE_ARRAY),
                'NAVIGATOR' => new VariableDependency(VariableNames::DOM_CRAWLER_NAVIGATOR),
            ],
            $this->getCollectionVariablePlaceholders($collection)
        );
    }

    public function testIterator(): void
    {
        $collectionValues = [
            'CRAWLER' => VariableNames::PANTHER_CRAWLER,
            'ENV' => VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
            'NAVIGATOR' => VariableNames::DOM_CRAWLER_NAVIGATOR,
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
