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
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

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
                'expectedMetadata' => Metadata::create(),
            ],
            'has metadata' => [
                'leftHandSide' => new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::ACTION_FACTORY),
                    'methodName'
                ),
                'rightHandSide' => new LiteralExpression('literal'),
                'comparison' => '!==',
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
                        new VariableDependency(VariableNames::ACTION_FACTORY),
                        'methodName'
                    ),
                    new LiteralExpression('value'),
                    '??'
                ),
                'expectedString' => '{{ ACTION_FACTORY }}->methodName() ?? value',
            ],
        ];
    }
}
