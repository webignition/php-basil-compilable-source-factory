<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Metadata;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;

class MetadataTest extends TestCase
{
    /**
     * @param non-empty-string[] $classNames
     * @param non-empty-string[] $variableNames
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(
        array $classNames,
        array $variableNames,
        ClassDependencyCollection $expectedClassDependencies,
        VariableDependencyCollection $expectedVariableDependencies
    ): void {
        $metadata = new Metadata($classNames, $variableNames);

        $this->assertEquals($expectedClassDependencies, $metadata->getClassDependencies());
        $this->assertEquals($expectedVariableDependencies, $metadata->getVariableDependencies());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'components set, correct types' => [
                'classNames' => [ClassName::class],
                'variableNames' => [DependencyName::PANTHER_CLIENT->value],
                'expectedClassDependencies' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                    ])
                ),
                'expectedVariableDependencies' => new VariableDependencyCollection([
                    DependencyName::PANTHER_CLIENT->value,
                ]),
            ],
        ];
    }

    public function testMerge(): void
    {
        $metadata1 = new Metadata(
            classNames: [
                ClassName::class,
            ],
            variableNames: [
                DependencyName::PANTHER_CLIENT->value,
                DependencyName::PHPUNIT_TEST_CASE->value,
            ],
        );

        $metadata2 = new Metadata(
            classNames: [
                ClassName::class,
                Metadata::class
            ],
            variableNames: [
                DependencyName::PHPUNIT_TEST_CASE->value,
                DependencyName::DOM_CRAWLER_NAVIGATOR->value,
            ],
        );

        $metadata = $metadata1->merge($metadata2);

        $expectedMetadata = new Metadata(
            classNames: [
                ClassName::class,
                Metadata::class,
            ],
            variableNames: [
                DependencyName::PANTHER_CLIENT->value,
                DependencyName::PHPUNIT_TEST_CASE->value,
                DependencyName::DOM_CRAWLER_NAVIGATOR->value,
            ],
        );

        $this->assertEquals($metadata, $expectedMetadata);
    }
}
