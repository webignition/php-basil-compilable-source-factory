<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ComparisonExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        ExpressionInterface $leftHandSide,
        ExpressionInterface $rightHandSide,
        string $comparison,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = new ComparisonExpression($leftHandSide, $rightHandSide, $comparison);

        $this->assertEquals($expectedMetadata, $expression->getMetadata());
        $this->assertSame($leftHandSide, $expression->getLeftHandSide());
        $this->assertSame($rightHandSide, $expression->getRightHandSide());
        $this->assertSame($comparison, $expression->getComparison());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'no metadata' => [
                'leftHandSide' => new LiteralExpression('5'),
                'rightHandSide' => new LiteralExpression('6'),
                'comparison' => '===',
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'leftHandSide' => new ObjectMethodInvocation(
                    new VariableDependency('DEPENDENCY'),
                    'methodName'
                ),
                'rightHandSide' => new LiteralExpression('literal'),
                'comparison' => '!==',
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
    public function testRender(ComparisonExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'literals, exact equals' => [
                'expression' => new ComparisonExpression(
                    new LiteralExpression('lhs'),
                    new LiteralExpression('rhs'),
                    '==='
                ),
                'expectedString' => 'lhs === rhs',
            ],
            'object method invocation and literal, null coalesce' => [
                'expression' => new ComparisonExpression(
                    new ObjectMethodInvocation(
                        new VariableDependency('DEPENDENCY'),
                        'methodName'
                    ),
                    new LiteralExpression('value'),
                    '??'
                ),
                'expectedString' => '{{ DEPENDENCY }}->methodName() ?? value',
            ],
        ];
    }
}
