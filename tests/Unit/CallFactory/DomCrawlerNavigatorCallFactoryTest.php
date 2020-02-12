<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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
    public function testCreateFindCall(ElementIdentifierInterface $identifier, CodeBlockInterface $expectedBlock)
    {
        $this->markTestSkipped();

        $statement = $this->factory->createFindCall($identifier);

        $this->assertEquals($expectedBlock, $statement);
    }

    public function createFindCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('find');
    }

    /**
     * @dataProvider createFindOneCallDataProvider
     */
    public function testCreateFindOneCall(ElementIdentifierInterface $identifier, CodeBlockInterface $expectedBlock)
    {
        $this->markTestSkipped();

        $statement = $this->factory->createFindOneCall($identifier);

        $this->assertEquals($expectedBlock, $statement);
    }

    public function createFindOneCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('findOne');
    }

    /**
     * @dataProvider createHasCallDataProvider
     */
    public function testCreateHasCall(ElementIdentifierInterface $identifier, CodeBlockInterface $expectedBlock)
    {
        $this->markTestSkipped();

        $statement = $this->factory->createHasCall($identifier);

        $this->assertEquals($expectedBlock, $statement);
    }

    public function createHasCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('has');
    }

    /**
     * @dataProvider createHasOneCallDataProvider
     */
    public function testCreateHasOneCall(ElementIdentifierInterface $identifier, CodeBlockInterface $expectedBlock)
    {
        $this->markTestSkipped();

        $statement = $this->factory->createHasOneCall($identifier);

        $this->assertEquals($expectedBlock, $statement);
    }

    public function createHasOneCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('hasOne');
    }

    private function createElementCallDataProvider(string $method): array
    {
        $testCases = $this->elementCallDataProvider();

        foreach ($testCases as $testCaseIndex => $testCase) {
            $block = new CodeBlock([
                $testCase['expectedBlock'],
            ]);

            $block->mutateLastStatement(function (string $content) use ($method) {
                return str_replace('{{ METHOD }}', $method, $content);
            });
        }

        return $testCases;
    }

    private function elementCallDataProvider(): array
    {
        return [
            'no parent, no ordinal position' => [
                'identifier' => new ElementIdentifier('.selector'),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'no parent, has ordinal position' => [
                'identifier' => new ElementIdentifier('.selector', 3),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(' .
                        'ElementIdentifier::fromJson(\'{"locator":".selector","position":3}\')' .
                        ')',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'no parent, has attribute' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name'),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'no parent, has ordinal position has attribute' => [
                'identifier' => new AttributeIdentifier('.selector', 'attribute_name', 3),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(' .
                        'ElementIdentifier::fromJson(\'{"locator":".selector","position":3}\')' .
                        ')',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, no ordinal position' => [
                'identifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(' .
                        'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                        ')',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, has ordinal position' => [
                'identifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                        '\'{"locator":".selector","parent":{"locator":".parent"},"position":2}\'' .
                        '))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, has attribute' => [
                'identifier' => (new AttributeIdentifier('.selector', 'attribute_name'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                        '\'{"locator":".selector","parent":{"locator":".parent"}}\'' .
                        '))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, has ordinal position, has attribute' => [
                'identifier' => (new AttributeIdentifier('.selector', 'attribute_name', 5))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                        '\'{"locator":".selector",' .
                        '"parent":{"locator":".parent"},"position":5}\'' .
                        '))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, has ordinal positions' => [
                'identifier' => (new ElementIdentifier('.selector', 3))
                    ->withParentIdentifier(new ElementIdentifier('.parent', 4)),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                        '\'{"locator":".selector","parent":{"locator":".parent","position":4},"position":3}\'' .
                        '))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
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
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(ElementIdentifier::fromJson(' .
                        '\'{"locator":".child","parent":{"locator":".parent","parent":{"locator":"grandparent"}}}\'' .
                        '))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementIdentifier::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
        ];
    }
}
