<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTest;
use webignition\BasilCompilableSourceFactory\VariableNames;

class DomCrawlerNavigatorCallFactoryTest extends AbstractResolvableTest
{
    private DomCrawlerNavigatorCallFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallDataProvider
     */
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
     * @return array[]
     */
    public function createFindCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('find');
    }

    /**
     * @dataProvider createFindOneCallDataProvider
     */
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
     * @return array[]
     */
    public function createFindOneCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('findOne');
    }

    /**
     * @dataProvider createHasCallDataProvider
     */
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
     * @return array[]
     */
    public function createHasCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('has');
    }

    /**
     * @dataProvider createHasOneCallDataProvider
     */
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
     * @return array[]
     */
    public function createHasOneCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('hasOne');
    }

    /**
     * @return array[]
     */
    private function createElementCallDataProvider(string $method): array
    {
        $testCases = $this->elementCallDataProvider();

        foreach ($testCases as $testCaseIndex => $testCase) {
            $testCase['expectedRenderedSource'] = str_replace(
                '{{ METHOD }}',
                $method,
                $testCase['expectedRenderedSource']
            );

            $testCases[$testCaseIndex] = $testCase;
        }

        return $testCases;
    }

    /**
     * @return array[]
     */
    private function elementCallDataProvider(): array
    {
        return [
            'literal expression' => [
                'elementIdentifierExpression' => new LiteralExpression('"literal expression"'),
                'expectedRenderedSource' => '{{ NAVIGATOR }}->{{ METHOD }}("literal expression")',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
