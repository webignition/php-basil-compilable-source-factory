<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\StaticObjectProperty;
use webignition\BasilCompilableSourceFactory\Model\IsAssigneeInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class AssignmentExpressionTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
    public function testCreate(
        IsAssigneeInterface $assignee,
        ExpressionInterface $value,
        string $operator,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = new AssignmentExpression($assignee, $value, $operator);

        $this->assertEquals($expectedMetadata, $expression->getMetadata());
        $this->assertSame($assignee, $expression->getAssignee());
        $this->assertSame($value, $expression->getValue());
        $this->assertSame($operator, $expression->getOperator());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'no metadata' => [
                'assignee' => new VariableName('lhs'),
                'value' => new LiteralExpression('6'),
                'operator' => '===',
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'assignee' => new VariableDependency(VariableNameEnum::PANTHER_CLIENT),
                'value' => new LiteralExpression('literal'),
                'operator' => '!==',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableNameEnum::PANTHER_CLIENT,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(AssignmentExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'variable name, literal' => [
                'expression' => new AssignmentExpression(
                    new VariableName('lhs'),
                    new LiteralExpression('rhs')
                ),
                'expectedString' => '$lhs = rhs',
            ],
            'object property access, literal' => [
                'expression' => new AssignmentExpression(
                    new ObjectPropertyAccessExpression(
                        new VariableDependency(VariableNameEnum::PANTHER_CLIENT),
                        'propertyName'
                    ),
                    new LiteralExpression('value')
                ),
                'expectedString' => '{{ CLIENT }}->propertyName = value',
            ],
            'variable dependency, literal' => [
                'expression' => new AssignmentExpression(
                    new VariableDependency(VariableNameEnum::PANTHER_CRAWLER),
                    new LiteralExpression('rhs')
                ),
                'expectedString' => '{{ CRAWLER }} = rhs',
            ],
            'static object property as assignee' => [
                'expression' => new AssignmentExpression(
                    new StaticObjectProperty(
                        new VariableDependency(VariableNameEnum::DOM_CRAWLER_NAVIGATOR),
                        'property'
                    ),
                    new LiteralExpression('rhs')
                ),
                'expectedString' => '{{ NAVIGATOR }}::property = rhs',
            ],
        ];
    }
}
