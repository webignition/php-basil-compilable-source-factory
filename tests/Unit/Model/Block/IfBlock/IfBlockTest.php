<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\IfBlock;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Block\IfBlock\IfBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class IfBlockTest extends AbstractResolvableTestCase
{
    public function testGetMetadata(): void
    {
        $expression = new ComparisonExpression(
            new ObjectMethodInvocation(
                new VariableDependency('IF_EXPRESSION_OBJECT'),
                'methodName'
            ),
            new LiteralExpression('value'),
            '==='
        );

        $body = new Body([
            new Statement(
                new AssignmentExpression(
                    new VariableDependency('BODY_DEPENDENCY'),
                    new StaticObjectMethodInvocation(
                        new StaticObject(\RuntimeException::class),
                        'staticMethodName'
                    )
                )
            ),
        ]);

        $ifBlock = new IfBlock($expression, $body);

        $expectedMetadata = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                new ClassName(\RuntimeException::class),
            ]),
            Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                'BODY_DEPENDENCY',
                'IF_EXPRESSION_OBJECT',
            ]),
        ]);

        $this->assertEquals($expectedMetadata, $ifBlock->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(IfBlock $ifBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $ifBlock);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'default' => [
                'ifBlock' => new IfBlock(
                    new ComparisonExpression(
                        new LiteralExpression('"value"'),
                        new LiteralExpression('"another value"'),
                        '!=='
                    ),
                    new Statement(
                        new ReturnExpression(
                            new LiteralExpression('"return value"')
                        )
                    )
                ),
                'expectedString' => 'if ("value" !== "another value") {' . "\n" .
                    '    return "return value";' . "\n" .
                    '}',
            ],
        ];
    }
}
