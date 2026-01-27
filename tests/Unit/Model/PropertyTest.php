<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClassObject;
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
                'property' => new Property('variable'),
                'expected' => new Metadata(),
            ],
            'variable placeholder' => [
                'property' => new Property(
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value,
                )->setIsDependency(),
                'expected' => new Metadata(
                    classNames: [],
                    variableNames: [
                        VariableName::ENVIRONMENT_VARIABLE_ARRAY->value,
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
                'property' => new Property('variable'),
                'expected' => '$variable',
            ],
            'variable placeholder' => [
                'property' => new Property(
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value,
                )->setIsDependency(),
                'expected' => '{{ ENV }}',
            ],
            'static object constant access (or enum), no alias' => [
                'property' => new Property(
                    'CONSTANT_NAME',
                    new ClassObject(
                        new ClassName(StatementStage::class),
                        true,
                    ),
                ),
                'expected' => 'StatementStage::CONSTANT_NAME',
            ],
            'object property access, no alias' => [
                'property' => new Property(
                    'property',
                    new Property(
                        'parent'
                    ),
                ),
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
