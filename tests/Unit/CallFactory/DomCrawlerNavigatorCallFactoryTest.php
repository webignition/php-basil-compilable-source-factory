<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;

class DomCrawlerNavigatorCallFactoryTest extends AbstractResolvableTestCase
{
    private DomCrawlerNavigatorCallFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    #[DataProvider('createFindCallDataProvider')]
    public function testCreateFindCall(
        ExpressionInterface $elementIdentifierExpression,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = $this->factory->createFindCall($elementIdentifierExpression);

        $this->assertRenderResolvable($expectedRenderedExpression, $expression);
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createFindCallDataProvider(): array
    {
        return self::createElementCallDataProvider('find');
    }

    #[DataProvider('createFindOneCallDataProvider')]
    public function testCreateFindOneCall(
        ExpressionInterface $elementIdentifierExpression,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = $this->factory->createFindOneCall($elementIdentifierExpression);

        $this->assertRenderResolvable($expectedRenderedExpression, $expression);
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createFindOneCallDataProvider(): array
    {
        return self::createElementCallDataProvider('findOne');
    }

    #[DataProvider('createHasCallDataProvider')]
    public function testCreateHasCall(
        ExpressionInterface $elementIdentifierExpression,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = $this->factory->createHasCall($elementIdentifierExpression);

        $this->assertRenderResolvable($expectedRenderedExpression, $expression);
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createHasCallDataProvider(): array
    {
        return self::createElementCallDataProvider('has');
    }

    #[DataProvider('createHasOneCallDataProvider')]
    public function testCreateHasOneCall(
        ExpressionInterface $elementIdentifierExpression,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ): void {
        $expression = $this->factory->createHasOneCall($elementIdentifierExpression);

        $this->assertRenderResolvable($expectedRenderedExpression, $expression);
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createHasOneCallDataProvider(): array
    {
        return self::createElementCallDataProvider('hasOne');
    }

    /**
     * @return array<mixed>
     */
    private static function createElementCallDataProvider(string $method): array
    {
        $testCases = [
            'literal expression' => [
                'elementIdentifierExpression' => new LiteralExpression('"literal expression"'),
                'expectedRenderedExpression' => '{{ NAVIGATOR }}->{{ METHOD }}("literal expression")',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        DependencyName::DOM_CRAWLER_NAVIGATOR->value,
                    ],
                ),
            ],
        ];

        foreach ($testCases as $testCaseIndex => $testCase) {
            $testCase['expectedRenderedExpression'] = str_replace(
                '{{ METHOD }}',
                $method,
                $testCase['expectedRenderedExpression']
            );

            $testCases[$testCaseIndex] = $testCase;
        }

        return $testCases;
    }
}
