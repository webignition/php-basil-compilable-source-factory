<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression\ArrayExpression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayPair;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class ArrayPairTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
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
                    '',
                    LiteralExpression::string('\'\'')
                ),
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'pair' => new ArrayPair(
                    '',
                    new MethodInvocation(
                        methodName: 'methodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: Type::STRING,
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    )
                ),
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
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
                    '',
                    LiteralExpression::string('\'\'')
                ),
                'expectedString' => "'' => '',",
            ],
            'empty key, string value' => [
                'pair' => new ArrayPair(
                    '',
                    LiteralExpression::string('\'value\'')
                ),
                'expectedString' => "'' => 'value',",
            ],
            'empty key, integer value' => [
                'pair' => new ArrayPair(
                    '',
                    LiteralExpression::integer(2)
                ),
                'expectedString' => "'' => 2,",
            ],
            'string value' => [
                'pair' => new ArrayPair(
                    'key',
                    LiteralExpression::string('\'value\'')
                ),
                'expectedString' => "'key' => 'value',",
            ],
            'array value, empty' => [
                'pair' => new ArrayPair(
                    'key',
                    new ArrayExpression([]),
                ),
                'expectedString' => "'key' => [],",
            ],
            'array value, non-empty' => [
                'pair' => new ArrayPair(
                    'key',
                    new ArrayExpression([
                        new ArrayPair(
                            'sub-key-1',
                            LiteralExpression::string('\'sub value 1\'')
                        ),
                        new ArrayPair(
                            'sub-key-2',
                            LiteralExpression::string('\'sub value 2\'')
                        ),
                        new ArrayPair(
                            'sub-key-3',
                            LiteralExpression::string('\'sub value 3\'')
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
