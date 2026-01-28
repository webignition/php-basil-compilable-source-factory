<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\IsAssigneeInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Property;
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
                'assignee' => Property::asVariable('lhs', Type::STRING),
                'value' => new LiteralExpression('6', Type::INTEGER),
                'operator' => '===',
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'assignee' => Property::asDependency(DependencyName::PANTHER_CLIENT),
                'value' => new LiteralExpression('literal', Type::STRING),
                'operator' => '!==',
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
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
                    Property::asVariable('lhs', Type::STRING),
                    new LiteralExpression('rhs', Type::STRING)
                ),
                'expectedString' => '$lhs = rhs',
            ],
            'variable dependency, literal' => [
                'expression' => new AssignmentExpression(
                    Property::asDependency(DependencyName::PANTHER_CRAWLER),
                    new LiteralExpression('rhs', Type::STRING)
                ),
                'expectedString' => '{{ CRAWLER }} = rhs',
            ],
            'static object property as assignee' => [
                'expression' => new AssignmentExpression(
                    new Property(
                        'property',
                        Type::STRING,
                        Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR)->setIsStatic(true)
                    ),
                    new LiteralExpression('rhs', Type::STRING)
                ),
                'expectedString' => '{{ NAVIGATOR }}::property = rhs',
            ],
        ];
    }
}
