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
     * @param DependencyName[]   $dependencyNames
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(
        array $classNames,
        array $dependencyNames,
        ClassDependencyCollection $expectedClassDependencies,
        VariableDependencyCollection $expectedVariableDependencies
    ): void {
        $metadata = new Metadata($classNames, $dependencyNames);

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
                'dependencyNames' => [DependencyName::PANTHER_CLIENT],
                'expectedClassDependencies' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                    ])
                ),
                'expectedVariableDependencies' => new VariableDependencyCollection([
                    DependencyName::PANTHER_CLIENT,
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
            dependencyNames: [
                DependencyName::PANTHER_CLIENT,
                DependencyName::PHPUNIT_TEST_CASE,
            ],
        );

        $metadata2 = new Metadata(
            classNames: [
                ClassName::class,
                Metadata::class
            ],
            dependencyNames: [
                DependencyName::PHPUNIT_TEST_CASE,
                DependencyName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        $metadata = $metadata1->merge($metadata2);

        $expectedMetadata = new Metadata(
            classNames: [
                ClassName::class,
                Metadata::class,
            ],
            dependencyNames: [
                DependencyName::PANTHER_CLIENT,
                DependencyName::PHPUNIT_TEST_CASE,
                DependencyName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        $this->assertEquals($metadata, $expectedMetadata);
    }
}
