<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\StatementInterface;
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
        string $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $statement = $this->factory->createFindCall($identifier);

        $this->assertInstanceOf(StatementInterface::class, $statement);
        $this->assertEquals($expectedContent, $statement->getContent());
        $this->assertMetadataEquals($expectedMetadata, $statement->getMetadata());
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
        string $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $statement = $this->factory->createFindOneCall($identifier);

        $this->assertInstanceOf(StatementInterface::class, $statement);
        $this->assertEquals($expectedContent, $statement->getContent());
        $this->assertMetadataEquals($expectedMetadata, $statement->getMetadata());
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
        string $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $statement = $this->factory->createHasCall($identifier);

        $this->assertInstanceOf(StatementInterface::class, $statement);
        $this->assertEquals($expectedContent, $statement->getContent());
        $this->assertMetadataEquals($expectedMetadata, $statement->getMetadata());
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
        string $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $statement = $this->factory->createHasOneCall($identifier);

        $this->assertInstanceOf(StatementInterface::class, $statement);
        $this->assertEquals($expectedContent, $statement->getContent());
        $this->assertMetadataEquals($expectedMetadata, $statement->getMetadata());
    }

    public function createHasOneCallDataProvider(): array
    {
        return $this->createElementCallDataProvider('hasOne');
    }

    private function createElementCallDataProvider(string $method): array
    {
        $testCases = $this->elementCallDataProvider();

        foreach ($testCases as $testCaseIndex => $testCase) {
            $expectedContent = str_replace('{{ METHOD }}', $method, $testCase['expectedContent']);

            $testCase['expectedContent'] = $expectedContent;
            $testCases[$testCaseIndex] = $testCase;
        }

        return $testCases;
    }

    private function elementCallDataProvider(): array
    {
        return [
            'no parent, no ordinal position' => [
                'identifier' => new DomIdentifier('.selector'),
                'expectedContent' => '{{ DOM_CRAWLER_NAVIGATOR }}->{{ METHOD }}(new ElementLocator(\'.selector\'))',
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ])),
            ],
            'no parent, has ordinal position' => [
                'identifier' => new DomIdentifier('.selector', 3),
                'expectedContent' => '{{ DOM_CRAWLER_NAVIGATOR }}->{{ METHOD }}(new ElementLocator(\'.selector\', 3))',
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ])),
            ],
            'has parent, no ordinal position' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedContent' => '{{ DOM_CRAWLER_NAVIGATOR }}'
                    .'->{{ METHOD }}(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ])),
            ],
            'has parent, has ordinal position' => [
                'identifier' => (new DomIdentifier('.selector', 2))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedContent' => '{{ DOM_CRAWLER_NAVIGATOR }}'
                    .'->{{ METHOD }}(new ElementLocator(\'.selector\', 2), new ElementLocator(\'.parent\'))',
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ])),
            ],
            'has parent, has ordinal positions' => [
                'identifier' => (new DomIdentifier('.selector', 3))
                    ->withParentIdentifier(new DomIdentifier('.parent', 4)),
                'expectedContent' => '{{ DOM_CRAWLER_NAVIGATOR }}'
                    .'->{{ METHOD }}(new ElementLocator(\'.selector\', 3), new ElementLocator(\'.parent\', 4))',
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ])),
            ],
        ];
    }
}
