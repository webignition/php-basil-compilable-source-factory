<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectConstant;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectConstantTest extends AbstractResolvableTestCase
{
    /**
     * @param array<mixed> $expectedContext
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(
        ClassName $className,
        string $property,
        array $expectedContext,
        MetadataInterface $expectedMetadata,
    ): void {
        $object = new ObjectConstant($className, $property);

        self::assertSame($expectedContext, $object->getContext());
        self::assertEquals($expectedMetadata, $object->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'constant, root namespace' => [
                'className' => new ClassName(\stdClass::class),
                'property' => 'FOO',
                'expectedContext' => [
                    'class' => '\stdClass',
                    'property' => 'FOO',
                ],
                'expectedMetadata' => new Metadata(
                    classNames: [\stdClass::class],
                ),
            ],
            'constant, non-root namespace' => [
                'className' => new ClassName(MethodDefinition::class),
                'property' => 'VISIBILITY_PUBLIC',
                'expectedContext' => [
                    'class' => 'MethodDefinition',
                    'property' => 'VISIBILITY_PUBLIC',
                ],
                'expectedMetadata' => new Metadata(
                    classNames: [MethodDefinition::class],
                ),
            ],
            'enum' => [
                'className' => new ClassName(VariableNameEnum::class),
                'property' => VariableNameEnum::PANTHER_CLIENT->value,
                'expectedContext' => [
                    'class' => 'VariableName',
                    'property' => 'CLIENT',
                ],
                'expectedMetadata' => new Metadata(
                    classNames: [VariableNameEnum::class],
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(ObjectConstant $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'constant, root namespace' => [
                'expression' => new ObjectConstant(
                    new ClassName(\stdClass::class),
                    'FOO'
                ),
                'expectedString' => '\stdClass::FOO',
            ],
            'constant, non-root namespace' => [
                'expression' => new ObjectConstant(
                    new ClassName(MethodDefinition::class),
                    'VISIBILITY_PUBLIC'
                ),
                'expectedString' => 'MethodDefinition::VISIBILITY_PUBLIC',
            ],
            'enum' => [
                'expression' => new ObjectConstant(
                    new ClassName(VariableNameEnum::class),
                    VariableNameEnum::PANTHER_CLIENT->value
                ),
                'expectedString' => 'VariableName::CLIENT',
            ],
        ];
    }
}
