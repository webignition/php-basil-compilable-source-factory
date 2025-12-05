<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\TryCatch;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class TryBlockTest extends AbstractResolvableTestCase
{
    public function testGetMetadata(): void
    {
        $body = new Body([
            new Statement(
                new AssignmentExpression(
                    new VariableDependency('DEPENDENCY'),
                    new StaticObjectMethodInvocation(
                        new StaticObject(\RuntimeException::class),
                        'staticMethodName'
                    )
                )
            ),
        ]);

        $tryBlock = new TryBlock($body);

        $expectedMetadata = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                new ClassNameCollection([
                    new ClassName(\RuntimeException::class),
                ])
            ),
            Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                'DEPENDENCY',
            ]),
        ]);

        $this->assertEquals($expectedMetadata, $tryBlock->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(TryBlock $tryBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $tryBlock);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'default' => [
                'tryBlock' => new TryBlock(
                    new Statement(
                        new LiteralExpression('"literal expression"')
                    )
                ),
                'expectedString' => 'try {' . "\n" .
                    '    "literal expression";' . "\n" .
                    '}',
            ],
            'empty return only' => [
                'tryBlock' => new TryBlock(
                    new Statement(
                        new ReturnExpression()
                    )
                ),
                'expectedString' => 'try {' . "\n" .
                    '    return;' . "\n" .
                    '}',
            ],
        ];
    }
}
