<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CastExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = new LiteralExpression('"literal"');
        $castExpression = new CastExpression($expression, 'string');

        $this->assertEquals($expression->getMetadata(), $castExpression->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(CastExpression $expression, string $expectedString): void
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
                'expression' => new CastExpression(
                    new LiteralExpression('100'),
                    'int'
                ),
                'expectedString' => '(int) (100)',
            ],
            'literal int as string' => [
                'expression' => new CastExpression(
                    new LiteralExpression('100'),
                    'string'
                ),
                'expectedString' => '(string) (100)',
            ],
            'empty array expression as object' => [
                'expression' => new CastExpression(new ArrayExpression([]), 'object'),
                'expectedString' => '(object) ([])',
            ],
            'empty closure expression as string' => [
                'expression' => new CastExpression(new ClosureExpression(new Body([])), 'string'),
                'expectedString' => '(string) ((function () {' . "\n"
                    . "\n"
                    . '})())',
            ],
            'comparison expression as int' => [
                'expression' => new CastExpression(
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
                'expression' => new CastExpression(
                    new CompositeExpression([
                        new LiteralExpression('$_ENV'),
                        new LiteralExpression('["secret"]'),
                    ]),
                    'string'
                ),
                'expectedString' => '(string) ($_ENV["secret"])',
            ],
            'object property access expression as string' => [
                'expression' => new CastExpression(
                    new ObjectPropertyAccessExpression(
                        new VariableDependency(VariableName::PANTHER_CLIENT),
                        'property'
                    ),
                    'string'
                ),
                'expectedString' => '(string) ({{ CLIENT }}->property)',
            ],
            'method invocation as string' => [
                'expression' => new CastExpression(
                    new MethodInvocation('methodName'),
                    'string'
                ),
                'expectedString' => '(string) (methodName())',
            ],
            'object method invocation as string' => [
                'expression' => new CastExpression(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableName::PANTHER_CLIENT),
                        'methodName'
                    ),
                    'string'
                ),
                'expectedString' => '(string) ({{ CLIENT }}->methodName())',
            ],
            'static object method invocation as string, class in root namespace' => [
                'expression' => new CastExpression(
                    new StaticObjectMethodInvocation(
                        new StaticObject('Object'),
                        'methodName'
                    ),
                    'string'
                ),
                'expectedString' => '(string) (\Object::methodName())',
            ],
            'static object method invocation as string, class not in root namespace' => [
                'expression' => new CastExpression(
                    new StaticObjectMethodInvocation(
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
