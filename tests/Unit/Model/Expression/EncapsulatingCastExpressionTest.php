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
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class EncapsulatingCastExpressionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $expression = LiteralExpression::string('"literal"');
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
                    LiteralExpression::integer(100),
                    Type::INTEGER
                ),
                'expectedString' => '(int) (100)',
            ],
            'literal int as string' => [
                'expression' => new EncapsulatingCastExpression(
                    LiteralExpression::integer(100),
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
                        new Body(),
                    ),
                    Type::STRING
                ),
                'expectedString' => <<<'EOD'
                    (string) ((function (): void {
                    
                    })())
                    EOD,
            ],
            'comparison expression as int' => [
                'expression' => new EncapsulatingCastExpression(
                    new ComparisonExpression(
                        LiteralExpression::string('"x"'),
                        LiteralExpression::string('"y"'),
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
                            LiteralExpression::void('$_ENV'),
                            LiteralExpression::void('["secret"]'),
                        ],
                        TypeCollection::string(),
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
                        type: TypeCollection::string(),
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
                        type: TypeCollection::string(),
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
                        type: TypeCollection::string(),
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
                        type: TypeCollection::string(),
                        parent: new StaticObject('Acme\Object'),
                    ),
                    Type::STRING
                ),
                'expectedString' => '(string) (Object::methodName())',
            ],
        ];
    }
}
