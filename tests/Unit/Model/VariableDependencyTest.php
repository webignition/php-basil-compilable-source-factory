<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;

class VariableDependencyTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(VariableDependency $dependency, MetadataInterface $expectedMetadata): void
    {
        $this->assertEquals($expectedMetadata, $dependency->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'variable dependency' => [
                'dependency' => new VariableDependency(VariableName::PANTHER_CLIENT->value),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT->value,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(VariableDependency $dependency, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $dependency);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'non-empty' => [
                'dependency' => new VariableDependency(VariableName::ENVIRONMENT_VARIABLE_ARRAY->value),
                'expectedString' => '{{ ENV }}',
            ],
        ];
    }
}
