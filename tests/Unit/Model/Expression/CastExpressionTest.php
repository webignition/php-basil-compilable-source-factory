<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CastExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = LiteralExpression::string('"literal"');
        $castExpression = new CastExpression($expression, Type::STRING);

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
            'literal int as int, is not cast' => [
                'expression' => new CastExpression(
                    LiteralExpression::integer(100),
                    Type::INTEGER
                ),
                'expectedString' => '100',
            ],
            'literal int as string, is cast' => [
                'expression' => new CastExpression(
                    LiteralExpression::integer(100),
                    Type::STRING
                ),
                'expectedString' => '(string) 100',
            ],
            'empty array expression as object, is cast' => [
                'expression' => new CastExpression(new ArrayExpression([]), Type::OBJECT),
                'expectedString' => '(object) []',
            ],
            'empty closure expression as string, is cast' => [
                'expression' => new CastExpression(
                    new ClosureExpression(new Body()),
                    Type::STRING
                ),
                'expectedString' => <<<'EOD'
                    (string) (function (): void {
                    
                    })()
                    EOD,
            ],
            'comparison expression as int, is encapsulated and cast' => [
                'expression' => new CastExpression(
                    new ComparisonExpression(
                        LiteralExpression::string('"x"'),
                        LiteralExpression::string('"y"'),
                        '==='
                    ),
                    Type::INTEGER
                ),
                'expectedString' => '(int) ("x" === "y")',
            ],
            'composite string expression as string, is not cast' => [
                'expression' => new CastExpression(
                    new CompositeExpression(
                        [
                            LiteralExpression::void('$_ENV'),
                            LiteralExpression::void('["secret"]'),
                        ],
                        TypeCollection::string(),
                    ),
                    Type::STRING
                ),
                'expectedString' => '$_ENV["secret"]',
            ],
            'string method invocation as string, is not cast' => [
                'expression' => new CastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::string(),
                    ),
                    Type::STRING,
                ),
                'expectedString' => 'methodName()',
            ],
            'integre method invocation as string, is not cast' => [
                'expression' => new CastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::integer(),
                    ),
                    Type::STRING,
                ),
                'expectedString' => '(string) methodName()',
            ],
            'static object method invocation as string, class in root namespace' => [
                'expression' => new CastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::integer(),
                        parent: new StaticObject('Object'),
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) \Object::methodName()',
            ],
            'static object method invocation as string, class not in root namespace' => [
                'expression' => new CastExpression(
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::integer(),
                        parent: new StaticObject('Acme\Object'),
                    ),
                    Type::STRING,
                ),
                'expectedString' => '(string) Object::methodName()',
            ],
        ];
    }
}
