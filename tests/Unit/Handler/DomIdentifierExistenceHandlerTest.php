<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\DomElementIdentifier\DomIdentifier;
use webignition\DomElementIdentifier\DomIdentifierInterface;
use webignition\DomElementLocator\ElementLocator;

class DomIdentifierExistenceHandlerTest extends AbstractTestCase
{
    /**
     * @var DomIdentifierExistenceHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = DomIdentifierExistenceHandler::createHandler();
    }

    /**
     * @dataProvider createForElementDataProvider
     */
    public function testCreateForElement(
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createForElement($identifier);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForElementDataProvider(): array
    {
        return [
            'no parent' => [
                'identifier' => new DomIdentifier('.selector'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
            'has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne('
                    . 'new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\')'
                    . ')',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
        ];
    }

    /**
     * @dataProvider createForCollectionDataProvider
     */
    public function testCreateForCollection(
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createForCollection($identifier);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForCollectionDataProvider(): array
    {
        return [
            'no parent' => [
                'identifier' => new DomIdentifier('.selector'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
            'has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has('
                    . 'new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\')'
                    . ')',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
        ];
    }

    /**
     * @dataProvider createForElementOrCollectionDataProvider
     */
    public function testCreateForElementOrCollection(
        DomIdentifierInterface $identifier,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->createForElementOrCollection($identifier);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForElementOrCollectionDataProvider(): array
    {
        return [
            'no attribute, no parent' => [
                'identifier' => new DomIdentifier('.selector'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
            'no attribute, has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has('
                    . 'new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\')'
                    . ')',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
            'has attribute, no parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withAttributeName('attribute_name'),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
            'has attribute, has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withAttributeName('attribute_name')
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne('
                    . 'new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\')'
                    . ')',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                    ])),
            ],
        ];
    }
}
