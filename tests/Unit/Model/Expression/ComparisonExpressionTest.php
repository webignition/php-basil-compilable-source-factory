<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ComparisonExpressionTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
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
                'leftHandSide' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                ),
                'rightHandSide' => new LiteralExpression('literal'),
                'comparison' => '!==',
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
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
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ),
                    new LiteralExpression('value'),
                    '??'
                ),
                'expectedString' => '{{ CLIENT }}->methodName() ?? value',
            ],
        ];
    }
}
