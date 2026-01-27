<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;

class PropertyTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(Property $property, MetadataInterface $expected): void
    {
        self::assertEquals($expected, $property->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'variable name' => [
                'property' => Property::asVariable('variable'),
                'expected' => new Metadata(),
            ],
            'variable placeholder' => [
                'property' => Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
                'expected' => new Metadata(
                    classNames: [],
                    dependencyNames: [
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ],
                ),
            ]
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(Property $property, string $expected): void
    {
        $this->assertRenderResolvable($expected, $property);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'variable name' => [
                'property' => Property::asVariable('variable'),
                'expected' => '$variable',
            ],
            'variable placeholder' => [
                'property' => Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
                'expected' => '{{ ENV }}',
            ],
            'static object constant access (or enum), no alias' => [
                'property' => Property::asClassConstant(new ClassName(StatementStage::class), 'CONSTANT_NAME'),
                'expected' => 'StatementStage::CONSTANT_NAME',
            ],
            'object property access, no alias' => [
                'property' => Property::asObjectProperty(new Property('parent'), 'property'),
                'expected' => '$parent->property',
            ],
            'method invocation object property access, no alias' => [
                'property' => new Property(
                    'property',
                    new MethodInvocation(
                        'methodName',
                        new MethodArguments(),
                        false,
                    ),
                ),
                'expected' => 'methodName()->property',
            ],
        ];
    }
}
