<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Statement;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

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
                'expression' => new VariableDependency(VariableName::PANTHER_CLIENT),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
            ],
            'method invocation' => [
                'expression' => new MethodInvocation('methodName'),
                'expectedMetadata' => new Metadata(),
            ],
            'object method invocation' => [
                'expression' => new ObjectMethodInvocation(
                    new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
                    'methodName'
                ),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
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
                    new VariableDependency(VariableName::PANTHER_CLIENT)
                ),
                'expectedString' => '{{ CLIENT }};',
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
                        new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
                        'methodName'
                    )
                ),
                'expectedString' => '{{ PHPUNIT }}->methodName();',
            ],
        ];
    }
}
