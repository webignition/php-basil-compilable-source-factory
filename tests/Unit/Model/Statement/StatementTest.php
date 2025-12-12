<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Statement;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

class StatementTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(ExpressionInterface $expression, MetadataInterface $expectedMetadata): void
    {
        $statement = new Statement($expression);

        $this->assertEquals($expectedMetadata, $statement->getMetadata());
        $this->assertSame($expression, $statement->getExpression());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'variable dependency' => [
                'expression' => new VariableDependency(VariableNames::ACTION_FACTORY),
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::ACTION_FACTORY,
                    ],
                ),
            ],
            'method invocation' => [
                'expression' => new MethodInvocation('methodName'),
                'expectedMetadata' => Metadata::create(),
            ],
            'object method invocation' => [
                'expression' => new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::ASSERTION_FACTORY),
                    'methodName'
                ),
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::ASSERTION_FACTORY,
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(StatementInterface $statement, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $statement);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'statement encapsulating variable dependency' => [
                'statement' => new Statement(
                    new VariableDependency(VariableNames::ACTION_FACTORY)
                ),
                'expectedString' => '{{ ACTION_FACTORY }};',
            ],
            'statement encapsulating method invocation' => [
                'statement' => new Statement(
                    new MethodInvocation('methodName')
                ),
                'expectedString' => 'methodName();',
            ],
            'statement encapsulating object method invocation' => [
                'statement' => new Statement(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::ASSERTION_FACTORY),
                        'methodName'
                    )
                ),
                'expectedString' => '{{ ASSERTION_FACTORY }}->methodName();',
            ],
        ];
    }
}
