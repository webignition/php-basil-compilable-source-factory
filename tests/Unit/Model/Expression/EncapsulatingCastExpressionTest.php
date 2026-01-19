<?php

declare(strict_types=1);

namespace Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class EncapsulatingCastExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = new LiteralExpression('"literal"');
        $castExpression = new EncapsulatingCastExpression($expression, 'string');

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
                    new LiteralExpression('100'),
                    'int'
                ),
                'expectedString' => '(int) (100)',
            ],
            'literal int as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new LiteralExpression('100'),
                    'string'
                ),
                'expectedString' => '(string) (100)',
            ],
            'empty array expression as object' => [
                'expression' => new EncapsulatingCastExpression(new ArrayExpression([]), 'object'),
                'expectedString' => '(object) ([])',
            ],
            'empty closure expression as string' => [
                'expression' => new EncapsulatingCastExpression(new ClosureExpression(new Body([])), 'string'),
                'expectedString' => '(string) ((function () {' . "\n"
                    . "\n"
                    . '})())',
            ],
            'comparison expression as int' => [
                'expression' => new EncapsulatingCastExpression(
                    new ComparisonExpression(
                        new LiteralExpression('"x"'),
                        new LiteralExpression('"y"'),
                        '==='
                    ),
                    'int'
                ),
                'expectedString' => '(int) ("x" === "y")',
            ],
            'composite expression as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new CompositeExpression([
                        new LiteralExpression('$_ENV'),
                        new LiteralExpression('["secret"]'),
                    ]),
                    'string'
                ),
                'expectedString' => '(string) ($_ENV["secret"])',
            ],
            'object property access expression as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new ObjectPropertyAccessExpression(
                        new VariableDependency(VariableName::PANTHER_CLIENT),
                        'property'
                    ),
                    'string'
                ),
                'expectedString' => '(string) ({{ CLIENT }}->property)',
            ],
            'method invocation as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new MethodInvocation('methodName'),
                    'string'
                ),
                'expectedString' => '(string) (methodName())',
            ],
            'object method invocation as string' => [
                'expression' => new EncapsulatingCastExpression(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableName::PANTHER_CLIENT),
                        'methodName'
                    ),
                    'string'
                ),
                'expectedString' => '(string) ({{ CLIENT }}->methodName())',
            ],
            'static object method invocation as string, class in root namespace' => [
                'expression' => new EncapsulatingCastExpression(
                    new ObjectMethodInvocation(
                        new StaticObject('Object'),
                        'methodName'
                    ),
                    'string'
                ),
                'expectedString' => '(string) (\Object::methodName())',
            ],
            'static object method invocation as string, class not in root namespace' => [
                'expression' => new EncapsulatingCastExpression(
                    new ObjectMethodInvocation(
                        new StaticObject('Acme\Object'),
                        'methodName'
                    ),
                    'string'
                ),
                'expectedString' => '(string) (Object::methodName())',
            ],
        ];
    }
}
