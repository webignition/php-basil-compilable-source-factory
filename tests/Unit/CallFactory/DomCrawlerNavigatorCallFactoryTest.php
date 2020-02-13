<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomCrawlerNavigatorCallFactoryTest extends AbstractTestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallDataProvider
     */
    public function testCreateFindCall(
        ElementIdentifierInterface $identifier,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ) {
        $expression = $this->factory->createFindCall($identifier);

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
        ElementIdentifierInterface $identifier,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ) {
        $expression = $this->factory->createFindOneCall($identifier);

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
        ElementIdentifierInterface $identifier,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ) {
        $expression = $this->factory->createHasCall($identifier);

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
        ElementIdentifierInterface $identifier,
        string $expectedRenderedExpression,
        MetadataInterface $expectedMetadata
    ) {
        $expression = $this->factory->createHasOneCall($identifier);

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
            'no parent, no ordinal position' => [
                'identifier' => new ElementIdentifier('.selector'),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'no parent, has ordinal position' => [
                'identifier' => new ElementIdentifier('.selector', 3),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","position":3}\')' .
                    ')',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'no parent, has attribute' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name'),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'no parent, has ordinal position has attribute' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name', 3),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","position":3}\')' .
                    ')',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'has parent, no ordinal position' => [
                'identifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ')',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'has parent, has ordinal position' => [
                'identifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                    '\'{"locator":".selector","parent":{"locator":".parent"},"position":2}\'' .
                    '))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'has parent, has attribute' => [
                'identifier' => (new AttributeIdentifier('.selector', 'attribute_name'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                    '\'{"locator":".selector","parent":{"locator":".parent"}}\'' .
                    '))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'has parent, has ordinal position, has attribute' => [
                'identifier' => (new AttributeIdentifier('.selector', 'attribute_name', 5))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                    '\'{"locator":".selector",' .
                    '"parent":{"locator":".parent"},"position":5}\'' .
                    '))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'has parent, has ordinal positions' => [
                'identifier' => (new ElementIdentifier('.selector', 3))
                    ->withParentIdentifier(new ElementIdentifier('.parent', 4)),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                    '\'{"locator":".selector","parent":{"locator":".parent","position":4},"position":3}\'' .
                    '))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'has attribute, has parents with attribute' => [
                'identifier' => (new AttributeIdentifier('.child', 'child_attr'))
                    ->withParentIdentifier(
                        (new AttributeIdentifier('.parent', 'parent_attr'))
                            ->withParentIdentifier(
                                new AttributeIdentifier('grandparent', 'gp_attr')
                            )
                    ),
                'expectedRenderedSource' =>
                    '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                    '\'{"locator":".child","parent":{"locator":".parent","parent":{"locator":"grandparent"}}}\'' .
                    '))',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
