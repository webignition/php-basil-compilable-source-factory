<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

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
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\DomElementLocator\ElementLocator;

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
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedBlock
    ) {
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
    public function testCreateFindOneCall(
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedBlock
    ) {
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
    public function testCreateHasCall(
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedBlock
    ) {
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
    public function testCreateHasOneCall(
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedBlock
    ) {
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
                'identifier' => new DomIdentifier('.selector'),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(new ElementLocator(\'.selector\'))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementLocator::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'no parent, has ordinal position' => [
                'identifier' => new DomIdentifier('.selector', 3),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->{{ METHOD }}(new ElementLocator(\'.selector\', 3))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementLocator::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, no ordinal position' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}->'
                        . '{{ METHOD }}(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementLocator::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, has ordinal position' => [
                'identifier' => (new DomIdentifier('.selector', 2))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}'
                        . '->{{ METHOD }}(new ElementLocator(\'.selector\', 2), new ElementLocator(\'.parent\'))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementLocator::class),
                            ]))
                            ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                                VariableNames::DOM_CRAWLER_NAVIGATOR,
                            ]))
                    )
                ]),
            ],
            'has parent, has ordinal positions' => [
                'identifier' => (new DomIdentifier('.selector', 3))
                    ->withParentIdentifier(new DomIdentifier('.parent', 4)),
                'expectedBlock' => new CodeBlock([
                    new Statement(
                        '{{ NAVIGATOR }}'
                        . '->{{ METHOD }}(new ElementLocator(\'.selector\', 3), new ElementLocator(\'.parent\', 4))',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(ElementLocator::class),
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
