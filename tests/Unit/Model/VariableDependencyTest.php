<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;

class VariableDependencyTest extends AbstractResolvableTestCase
{
    /**
     * @param VariableNames::* $name
     *
     * @dataProvider constructDataProvider
     */
    public function testConstruct(string $name): void
    {
        $dependency = new VariableDependency($name);

        $this->assertSame($name, $dependency->getName());
    }

    /**
     * @return array<mixed>
     */
    public static function constructDataProvider(): array
    {
        return [
            'default' => [
                'name' => 'DEPENDENCY',
            ],
        ];
    }

    /**
     * @dataProvider getMetadataDataProvider
     */
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
                'dependency' => new VariableDependency(VariableNames::ACTION_FACTORY),
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::ACTION_FACTORY,
                    ]
                ),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
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
                'dependency' => new VariableDependency(VariableNames::ENVIRONMENT_VARIABLE_ARRAY),
                'expectedString' => '{{ ENV }}',
            ],
        ];
    }
}
