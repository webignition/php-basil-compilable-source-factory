<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Model\VariablePlaceholderInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ObjectPropertyAccessExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        VariablePlaceholderInterface $objectPlaceholder,
        string $property,
        MetadataInterface $expectedMetadata
    ): void {
        $invocation = new ObjectPropertyAccessExpression($objectPlaceholder, $property);

        $this->assertSame($objectPlaceholder, $invocation->getObjectPlaceholder());
        $this->assertSame($property, $invocation->getProperty());
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'has resolvable placeholder' => [
                'objectPlaceholder' => new VariableDependency('OBJECT'),
                'property' => 'propertyName',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'OBJECT',
                    ]),
                ]),
            ],
            'has resolving placeholder' => [
                'objectPlaceholder' => new VariableName('object'),
                'property' => 'propertyName',
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ObjectPropertyAccessExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'has resolvable placeholder' => [
                'expression' => new ObjectPropertyAccessExpression(
                    new VariableDependency('OBJECT'),
                    'propertyName'
                ),
                'expectedString' => '{{ OBJECT }}->propertyName',
            ],
            'has resolving placeholder' => [
                'expression' => new ObjectPropertyAccessExpression(
                    new VariableName('object'),
                    'propertyName'
                ),
                'expectedString' => '$object->propertyName',
            ],
        ];
    }
}
