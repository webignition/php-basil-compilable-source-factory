<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class StatementTest extends AbstractResolvableTestCase
{
    #[DataProvider('createDataProvider')]
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
                'expression' => new VariableDependency(DependencyName::PANTHER_CLIENT->value),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        DependencyName::PANTHER_CLIENT->value,
                    ],
                ),
            ],
            'method invocation' => [
                'expression' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedMetadata' => new Metadata(),
            ],
            'object method invocation' => [
                'expression' => new ObjectMethodInvocation(
                    object: new VariableDependency(DependencyName::PHPUNIT_TEST_CASE->value),
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                ),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        DependencyName::PHPUNIT_TEST_CASE->value,
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
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
                    new VariableDependency(DependencyName::PANTHER_CLIENT->value)
                ),
                'expectedString' => '{{ CLIENT }};',
            ],
            'statement encapsulating method invocation' => [
                'statement' => new Statement(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    )
                ),
                'expectedString' => 'methodName();',
            ],
            'statement encapsulating object method invocation' => [
                'statement' => new Statement(
                    new ObjectMethodInvocation(
                        object: new VariableDependency(DependencyName::PHPUNIT_TEST_CASE->value),
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    )
                ),
                'expectedString' => '{{ PHPUNIT }}->methodName();',
            ],
        ];
    }
}
