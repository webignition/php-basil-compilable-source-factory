<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class CompositeExpressionTest extends AbstractResolvableTestCase
{
    /**
     * @param array<mixed> $expressions
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(array $expressions, MetadataInterface $expectedMetadata): void
    {
        $expression = new CompositeExpression($expressions);

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
                    new LiteralExpression('[\'KEY\']')
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
                'expression' => new CompositeExpression([]),
                'expectedString' => '',
            ],
            'single literal' => [
                'expression' => new CompositeExpression([
                    new LiteralExpression('literal1'),
                ]),
                'expectedString' => 'literal1',
            ],
            'multiple literals' => [
                'expression' => new CompositeExpression([
                    new LiteralExpression('literal1'),
                    new LiteralExpression('literal2'),
                    new LiteralExpression('literal3'),
                ]),
                'expectedString' => 'literal1literal2literal3',
            ],
            'variable dependency' => [
                'expression' => new CompositeExpression([
                    Property::asDependency(DependencyName::PANTHER_CLIENT),
                ]),
                'expectedString' => '{{ CLIENT }}',
            ],
            'variable dependency and array access' => [
                'expression' => new CompositeExpression([
                    Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
                    new LiteralExpression('[\'KEY\']')
                ]),
                'expectedString' => '{{ ENV }}[\'KEY\']',
            ],
            'resolvable expression, stringable expression, resolvable expression' => [
                'expression' => new CompositeExpression([
                    new EncapsulatingCastExpression(
                        new LiteralExpression('1'),
                        'string'
                    ),
                    new LiteralExpression(' . \'x\' . '),
                    new EncapsulatingCastExpression(
                        new LiteralExpression('2'),
                        'string'
                    ),
                ]),
                'expectedString' => '(string) (1) . \'x\' . (string) (2)',
            ],
        ];
    }
}
