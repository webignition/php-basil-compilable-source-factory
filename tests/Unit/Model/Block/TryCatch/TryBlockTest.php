<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Block\TryCatch;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class TryBlockTest extends AbstractResolvableTestCase
{
    public function testGetMetadata(): void
    {
        $body = new Body([
            new Statement(
                new AssignmentExpression(
                    Property::asDependency(DependencyName::PANTHER_CLIENT),
                    new MethodInvocation(
                        methodName: 'staticMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::string(),
                        parent: new StaticObject(\RuntimeException::class),
                    )
                )
            ),
        ]);

        $tryBlock = new TryBlock($body);

        $expectedMetadata = new Metadata(
            classNames: [
                \RuntimeException::class,
            ],
            dependencyNames: [
                DependencyName::PANTHER_CLIENT,
            ]
        );

        $this->assertEquals($expectedMetadata, $tryBlock->getMetadata());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(TryBlock $tryBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $tryBlock);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'default' => [
                'tryBlock' => new TryBlock(
                    new Statement(
                        LiteralExpression::string('"literal expression"')
                    )
                ),
                'expectedString' => 'try {' . "\n"
                    . '    "literal expression";' . "\n"
                    . '}',
            ],
        ];
    }
}
