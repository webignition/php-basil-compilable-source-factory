<?php

declare(strict_types=1);

namespace Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class EncapsulatingCastExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = new LiteralExpression('"literal"', Type::STRING);
        $castExpression = new EncapsulatingCastExpression($expression, Type::STRING);

        $this->assertEquals($expression->getMetadata(), $castExpression->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(EncapsulatingCastExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'literal int as int' => [
                'expression' => new EncapsulatingCastExpression(
                    new LiteralExpression('100', Type::INTEGER),
                    Type::INTEGER
                ),
                'expectedString' => '(int) (100)',
            ],
            'literal int as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new LiteralExpression('100', Type::INTEGER),
                    Type::STRING
                ),
                'expectedString' => '(string) (100)',
            ],
            'empty array expression as object' => [
                'expression' => new EncapsulatingCastExpression(new ArrayExpression([]), Type::OBJECT),
                'expectedString' => '(object) ([])',
            ],
            'empty closure expression as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new ClosureExpression(
                        new Body([]),
                        Type::STRING
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) ((function () {' . "\n"
                    . "\n"
                    . '})())',
            ],
            'comparison expression as int' => [
                'expression' => new EncapsulatingCastExpression(
                    new ComparisonExpression(
                        new LiteralExpression('"x"', Type::STRING),
                        new LiteralExpression('"y"', Type::STRING),
                        '==='
                    ),
                    Type::INTEGER
                ),
                'expectedString' => '(int) ("x" === "y")',
            ],
            'composite expression as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new CompositeExpression(
                        [
                            new LiteralExpression('$_ENV', Type::ARRAY),
                            new LiteralExpression('["secret"]', Type::VOID),
                        ],
                        Type::STRING,
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) ($_ENV["secret"])',
            ],
            'method invocation as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: Type::STRING,
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) (methodName())',
            ],
            'object method invocation as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: Type::STRING,
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) ({{ CLIENT }}->methodName())',
            ],
            'static object method invocation as string, class in root namespace' => [
                'expression' => new EncapsulatingCastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: Type::STRING,
                        parent: new StaticObject('Object'),
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) (\Object::methodName())',
            ],
            'static object method invocation as string, class not in root namespace' => [
                'expression' => new EncapsulatingCastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: Type::STRING,
                        parent: new StaticObject('Acme\Object'),
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) (Object::methodName())',
            ],
        ];
    }
}
