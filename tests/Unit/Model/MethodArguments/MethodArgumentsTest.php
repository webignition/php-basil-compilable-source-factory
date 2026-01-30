<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodArguments;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class MethodArgumentsTest extends AbstractResolvableTestCase
{
    /**
     * @param ExpressionInterface[] $arguments
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(
        array $arguments,
        string $format,
        MetadataInterface $expectedMetadata
    ): void {
        $methodArguments = new MethodArguments($arguments, $format);

        $this->assertSame($arguments, $methodArguments->getArguments());
        $this->assertSame($format, $methodArguments->getFormat());
        $this->assertEquals($expectedMetadata, $methodArguments->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty, inline' => [
                'arguments' => [],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(),
            ],
            'empty, stacked' => [
                'arguments' => [],
                'format' => MethodArgumentsInterface::FORMAT_STACKED,
                'expectedMetadata' => new Metadata(),
            ],
            'single argument' => [
                'arguments' => [
                    LiteralExpression::integer(1),
                ],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(),
            ],
            'multiple arguments' => [
                'arguments' => [
                    LiteralExpression::integer(2),
                    LiteralExpression::string("\\'single-quoted value\\'"),
                    LiteralExpression::string('"double-quoted value"'),
                ],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(),
            ],
            'has metadata' => [
                'arguments' => [
                    new MethodInvocation(
                        methodName: 'staticMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::string(),
                        parent: new StaticObject(ClassName::class),
                    )
                ],
                'format' => MethodArgumentsInterface::FORMAT_INLINE,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ClassName::class,
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(MethodArguments $arguments, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $arguments);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty, inline' => [
                'arguments' => new MethodArguments([]),
                'expectedString' => '',
            ],
            'empty, stacked' => [
                'arguments' => new MethodArguments([], MethodArgumentsInterface::FORMAT_STACKED),
                'expectedString' => '',
            ],
            'has arguments, inline' => [
                'arguments' => new MethodArguments([
                    LiteralExpression::integer(1),
                    LiteralExpression::string("\\'single-quoted value\\'"),
                ]),
                'expectedString' => <<< 'EOD'
                    1, \'single-quoted value\'
                    EOD,
            ],
            'has arguments, stacked' => [
                'arguments' => new MethodArguments(
                    [
                        LiteralExpression::integer(1),
                        LiteralExpression::string("\\'single-quoted value\\'"),
                    ],
                    MethodArgumentsInterface::FORMAT_STACKED
                ),
                'expectedString' => <<< 'EOD'

                        1,
                        \'single-quoted value\',
                    
                    EOD,
            ],
            'indent stacked multi-line arguments' => [
                'arguments' => new MethodArguments(
                    [
                        new MethodInvocation(
                            methodName: 'find',
                            arguments: new MethodArguments([
                                new MethodInvocation(
                                    methodName: 'fromJson',
                                    arguments: new MethodArguments([
                                        LiteralExpression::string(
                                            '{' . "\n" . '    "locator": ".selector"' . "\n" . '}',
                                        ),
                                    ]),
                                    mightThrow: false,
                                    type: TypeCollection::string(),
                                    parent: new StaticObject(MethodArguments::class),
                                )
                            ]),
                            mightThrow: false,
                            type: TypeCollection::string(),
                            parent: Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR),
                        ),
                        new ClosureExpression(
                            new Body(
                                new BodyContentCollection()
                                    ->append(
                                        new Statement(
                                            new AssignmentExpression(
                                                Property::asStringVariable('variable'),
                                                LiteralExpression::integer(100)
                                            )
                                        )
                                    )
                                    ->append(
                                        new EmptyLine()
                                    )
                                    ->append(
                                        new Statement(
                                            new ReturnExpression(
                                                Property::asIntegerVariable('variable'),
                                            )
                                        )
                                    )
                            ),
                        ),
                    ],
                    MethodArgumentsInterface::FORMAT_STACKED
                ),
                'expectedString' => <<< 'EOD'
                    
                        {{ NAVIGATOR }}->find(MethodArguments::fromJson({
                            "locator": ".selector"
                        })),
                        (function (): int {
                            $variable = 100;
                    
                            return $variable;
                        })(),

                    EOD,
            ],
            'single array expression, pair contains inner array' => [
                'arguments' => new MethodArguments(
                    [
                        ArrayExpression::fromArray([
                            'name' => new MethodInvocation(
                                methodName: 'dataName',
                                arguments: new MethodArguments(),
                                mightThrow: false,
                                type: TypeCollection::string(),
                                parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                            ),
                            'data' => [
                                'key1' => 'value1',
                                'key2' => 'value2',
                            ],
                        ])
                    ]
                ),
                'expectedString' => <<< 'EOD'
                    [
                        'name' => {{ CLIENT }}->dataName(),
                        'data' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ],
                    ]
                    EOD,
            ],
        ];
    }
}
