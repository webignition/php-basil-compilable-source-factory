<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;

class DomCrawlerNavigatorCallFactoryTest extends \PHPUnit\Framework\TestCase
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
    ) {
        $expression = $this->factory->createFindCall($elementIdentifierExpression);

        $this->assertSame($expectedRenderedExpression, $expression->render());
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

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
    ) {
        $expression = $this->factory->createFindOneCall($elementIdentifierExpression);

        $this->assertSame($expectedRenderedExpression, $expression->render());
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

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
    ) {
        $expression = $this->factory->createHasCall($elementIdentifierExpression);

        $this->assertSame($expectedRenderedExpression, $expression->render());
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

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
    ) {
        $expression = $this->factory->createHasOneCall($elementIdentifierExpression);

        $this->assertSame($expectedRenderedExpression, $expression->render());
        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    public function createHasOneCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('hasOne');
    }

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

    private function elementCallDataProvider(): array
    {
        return [
            'literal expression' => [
                'elementIdentifierExpression' => new LiteralExpression('"literal expression"'),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}("literal expression")',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
