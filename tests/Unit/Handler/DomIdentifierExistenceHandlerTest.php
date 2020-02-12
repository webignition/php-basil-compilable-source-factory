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
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

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
        ElementIdentifierInterface $identifier,
        string $assertionFailureMessage,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $this->markTestSkipped();

        $source = $this->handler->createForElement($identifier, $assertionFailureMessage);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForElementDataProvider(): array
    {
        return [
            'no parent' => [
                'identifier' => new ElementIdentifier('.selector'),
                'assertionFailureMessage' => 'false is not true',
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }}, \'false is not true\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                'identifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'assertionFailureMessage' => 'false is not true',
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ')',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }}, \'false is not true\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
        ElementIdentifierInterface $identifier,
        string $assertionFailureMessage,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $this->markTestSkipped();

        $source = $this->handler->createForCollection($identifier, $assertionFailureMessage);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function createForCollectionDataProvider(): array
    {
        return [
            'no parent' => [
                'identifier' => new ElementIdentifier('.selector'),
                'assertionFailureMessage' => 'false is not true',
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }}, \'false is not true\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                'identifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'assertionFailureMessage' => 'false is not true',
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                        'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ')',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }}, \'false is not true\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
