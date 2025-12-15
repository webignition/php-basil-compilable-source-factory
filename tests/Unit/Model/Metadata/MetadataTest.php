<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Metadata;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;

class MetadataTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param non-empty-string[] $classNames
     * @param VariableName::*[]  $variableNames
     */
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
            'empty' => [
                'classNames' => [],
                'variableNames' => [],
                'expectedClassDependencies' => new ClassDependencyCollection(),
                'expectedVariableDependencies' => new VariableDependencyCollection(),
            ],
            'components set, correct types' => [
                'classNames' => [ClassName::class],
                'variableNames' => [VariableName::PANTHER_CLIENT],
                'expectedClassDependencies' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                    ])
                ),
                'expectedVariableDependencies' => new VariableDependencyCollection([
                    VariableName::PANTHER_CLIENT,
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
                VariableName::PANTHER_CLIENT,
                VariableName::ASSERTION_FACTORY,
            ],
        );

        $metadata2 = new Metadata(
            classNames: [
                ClassName::class,
                Metadata::class
            ],
            variableNames: [
                VariableName::ASSERTION_FACTORY,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        $metadata = $metadata1->merge($metadata2);

        $expectedMetadata = new Metadata(
            classNames: [
                ClassName::class,
                Metadata::class,
            ],
            variableNames: [
                VariableName::PANTHER_CLIENT,
                VariableName::ASSERTION_FACTORY,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        $this->assertEquals($metadata, $expectedMetadata);
    }
}
