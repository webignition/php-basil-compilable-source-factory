<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayKey;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayPair;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ArrayPairTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider getMetadataDataProvider
     */
    public function testGetMetadata(ArrayPair $pair, MetadataInterface $expectedMetadata): void
    {
        self::assertEquals($expectedMetadata, $pair->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'no metadata' => [
                'pair' => new ArrayPair(
                    new ArrayKey(''),
                    new LiteralExpression('\'\'')
                ),
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'pair' => new ArrayPair(
                    new ArrayKey(''),
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableName::PANTHER_CLIENT),
                        'methodName'
                    )
                ),
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ]
                ),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ArrayPair $pair, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $pair);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty key, empty string value' => [
                'pair' => new ArrayPair(
                    new ArrayKey(''),
                    new LiteralExpression('\'\'')
                ),
                'expectedString' => "'' => '',",
            ],
            'empty key, string value' => [
                'pair' => new ArrayPair(
                    new ArrayKey(''),
                    new LiteralExpression('\'value\'')
                ),
                'expectedString' => "'' => 'value',",
            ],
            'empty key, integer value' => [
                'pair' => new ArrayPair(
                    new ArrayKey(''),
                    new LiteralExpression('2')
                ),
                'expectedString' => "'' => 2,",
            ],
            'string value' => [
                'pair' => new ArrayPair(
                    new ArrayKey('key'),
                    new LiteralExpression('\'value\'')
                ),
                'expectedString' => "'key' => 'value',",
            ],
            'array value, empty' => [
                'pair' => new ArrayPair(
                    new ArrayKey('key'),
                    new ArrayExpression([]),
                ),
                'expectedString' => "'key' => [],",
            ],
            'array value, non-empty' => [
                'pair' => new ArrayPair(
                    new ArrayKey('key'),
                    new ArrayExpression([
                        new ArrayPair(
                            new ArrayKey('sub-key-1'),
                            new LiteralExpression('\'sub value 1\'')
                        ),
                        new ArrayPair(
                            new ArrayKey('sub-key-2'),
                            new LiteralExpression('\'sub value 2\'')
                        ),
                        new ArrayPair(
                            new ArrayKey('sub-key-3'),
                            new LiteralExpression('\'sub value 3\'')
                        ),
                    ]),
                ),
                'expectedString' => "'key' => [\n"
                    . "    'sub-key-1' => 'sub value 1',\n"
                    . "    'sub-key-2' => 'sub value 2',\n"
                    . "    'sub-key-3' => 'sub value 3',\n"
                    . '],',
            ],
        ];
    }
}
