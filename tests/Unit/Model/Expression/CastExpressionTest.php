<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\FooMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CastExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = new LiteralExpression('"literal"');
        $castExpression = new CastExpression($expression, 'string');

        $this->assertEquals($expression->getMetadata(), $castExpression->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
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
                'expectedString' => '(int) 100',
            ],
            'literal int as string' => [
                'expression' => new CastExpression(
                    new LiteralExpression('100'),
                    'string'
                ),
                'expectedString' => '(string) 100',
            ],
            'empty array expression as object' => [
                'expression' => new CastExpression(new ArrayExpression([]), 'object'),
                'expectedString' => '(object) []',
            ],
            'empty closure expression as string' => [
                'expression' => new CastExpression(new ClosureExpression(new Body([])), 'string'),
                'expectedString' => '(string) (function () {' . "\n"
                    . "\n"
                    . '})()',
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
                'expectedString' => '(int) "x" === "y"',
            ],
            'composite expression as string' => [
                'expression' => new CastExpression(
                    new CompositeExpression([
                        new LiteralExpression('$_ENV'),
                        new LiteralExpression('["secret"]'),
                    ]),
                    'string'
                ),
                'expectedString' => '(string) $_ENV["secret"]',
            ],
            'method invocation as string' => [
                'expression' => new CastExpression(
                    new FooMethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                    ),
                    'string'
                ),
                'expectedString' => '(string) methodName()',
            ],
            'object method invocation as string' => [
                'expression' => new CastExpression(
                    new FooMethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ),
                    'string'
                ),
                'expectedString' => '(string) {{ CLIENT }}->methodName()',
            ],
            'static object method invocation as string, class in root namespace' => [
                'expression' => new CastExpression(
                    new FooMethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        parent: new StaticObject('Object'),
                    ),
                    'string'
                ),
                'expectedString' => '(string) \Object::methodName()',
            ],
            'static object method invocation as string, class not in root namespace' => [
                'expression' => new CastExpression(
                    new FooMethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        parent: new StaticObject('Acme\Object'),
                    ),
                    'string'
                ),
                'expectedString' => '(string) Object::methodName()',
            ],
        ];
    }
}
