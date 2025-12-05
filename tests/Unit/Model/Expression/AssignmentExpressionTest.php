<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class AssignmentExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        ExpressionInterface $variable,
        ExpressionInterface $value,
        string $operator,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = new AssignmentExpression($variable, $value, $operator);

        $this->assertEquals($expectedMetadata, $expression->getMetadata());
        $this->assertSame($variable, $expression->getVariable());
        $this->assertSame($value, $expression->getValue());
        $this->assertSame($operator, $expression->getOperator());
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no metadata' => [
                'variable' => new LiteralExpression('5'),
                'value' => new LiteralExpression('6'),
                'operator' => '===',
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'variable' => new ObjectMethodInvocation(
                    new VariableDependency('DEPENDENCY'),
                    'methodName'
                ),
                'value' => new LiteralExpression('literal'),
                'operator' => '!==',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        'DEPENDENCY',
                    ])
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(AssignmentExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'literals, assignment' => [
                'expression' => new AssignmentExpression(
                    new LiteralExpression('lhs'),
                    new LiteralExpression('rhs')
                ),
                'expectedString' => 'lhs = rhs',
            ],
            'object property access and literal, assignment' => [
                'expression' => new AssignmentExpression(
                    new ObjectPropertyAccessExpression(
                        new VariableDependency('DEPENDENCY'),
                        'propertyName'
                    ),
                    new LiteralExpression('value')
                ),
                'expectedString' => '{{ DEPENDENCY }}->propertyName = value',
            ],
        ];
    }
}
