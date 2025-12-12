<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Metadata;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;

class MetadataTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param array<mixed> $components
     */
    public function testCreate(
        array $components,
        ClassDependencyCollection $expectedClassDependencies,
        VariableDependencyCollection $expectedVariableDependencies
    ): void {
        $metadata = new Metadata($components);

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
                'components' => [],
                'expectedClassDependencies' => new ClassDependencyCollection(),
                'expectedVariableDependencies' => new VariableDependencyCollection(),
            ],
            'components set, incorrect types' => [
                'components' => [
                    Metadata::KEY_CLASS_DEPENDENCIES => 'string',
                    Metadata::KEY_VARIABLE_DEPENDENCIES => 'string',
                ],
                'expectedClassDependencies' => new ClassDependencyCollection(),
                'expectedVariableDependencies' => new VariableDependencyCollection(),
            ],
            'components set, correct types' => [
                'components' => [
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ClassName::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::ACTION_FACTORY,
                    ]),
                ],
                'expectedClassDependencies' => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                    ])
                ),
                'expectedVariableDependencies' => new VariableDependencyCollection([
                    VariableNames::ACTION_FACTORY,
                ]),
            ],
        ];
    }

    public function testMerge(): void
    {
        $metadata1 = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                new ClassNameCollection([
                    new ClassName(ClassName::class),
                ])
            ),
            Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                VariableNames::ACTION_FACTORY,
                VariableNames::ASSERTION_FACTORY,
            ]),
        ]);

        $metadata2 = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                new ClassNameCollection([
                    new ClassName(ClassName::class),
                    new ClassName(Metadata::class),
                ])
            ),
            Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                VariableNames::ASSERTION_FACTORY,
                VariableNames::DOM_CRAWLER_NAVIGATOR,
            ]),
        ]);

        $metadata = $metadata1->merge($metadata2);

        $this->assertEquals(
            $metadata,
            new Metadata([
                Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                    new ClassNameCollection([
                        new ClassName(ClassName::class),
                        new ClassName(Metadata::class),
                    ])
                ),
                Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                    VariableNames::ACTION_FACTORY,
                    VariableNames::ASSERTION_FACTORY,
                    VariableNames::DOM_CRAWLER_NAVIGATOR,
                ]),
            ])
        );
    }
}
