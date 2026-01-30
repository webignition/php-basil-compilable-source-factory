<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CompositeExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @param array<mixed> $expressions
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(array $expressions, MetadataInterface $expectedMetadata): void
    {
        $expression = new CompositeExpression($expressions, TypeCollection::string());

        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty' => [
                'expressions' => [],
                'expectedMetadata' => new Metadata(),
            ],
            'variable dependency' => [
                'expressions' => [
                    Property::asDependency(DependencyName::PANTHER_CLIENT),
                ],
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ]
                ),
            ],
            'variable dependency and array access' => [
                'expressions' => [
                    Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
                    LiteralExpression::void('[\'KEY\']')
                ],
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::ENVIRONMENT_VARIABLE_ARRAY,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(CompositeExpression $expression, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $expression);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty' => [
                'expression' => new CompositeExpression([], TypeCollection::void()),
                'expectedString' => '',
            ],
            'single literal' => [
                'expression' => new CompositeExpression(
                    [
                        LiteralExpression::string('"literal1"'),
                    ],
                    TypeCollection::string(),
                ),
                'expectedString' => '"literal1"',
            ],
            'multiple literals' => [
                'expression' => new CompositeExpression(
                    [
                        LiteralExpression::string('"literal1"'),
                        LiteralExpression::string('"literal2"'),
                        LiteralExpression::string('"literal3"'),
                    ],
                    TypeCollection::string(),
                ),
                'expectedString' => '"literal1""literal2""literal3"',
            ],
            'variable dependency' => [
                'expression' => new CompositeExpression(
                    [
                        Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ],
                    TypeCollection::string(),
                ),
                'expectedString' => '{{ CLIENT }}',
            ],
            'variable dependency and array access' => [
                'expression' => new CompositeExpression(
                    [
                        Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
                        LiteralExpression::void('[\'KEY\']')
                    ],
                    TypeCollection::string(),
                ),
                'expectedString' => '{{ ENV }}[\'KEY\']',
            ],
            'resolvable expression, stringable expression, resolvable expression' => [
                'expression' => new CompositeExpression(
                    [
                        new CastExpression(
                            new EncapsulatedExpression(
                                LiteralExpression::integer(1),
                            ),
                            Type::STRING,
                        ),
                        LiteralExpression::void(' . \'x\' . '),
                        new CastExpression(
                            new EncapsulatedExpression(
                                LiteralExpression::integer(2),
                            ),
                            Type::STRING,
                        ),
                    ],
                    TypeCollection::string(),
                ),
                'expectedString' => '(string) (1) . \'x\' . (string) (2)',
            ],
        ];
    }
}
