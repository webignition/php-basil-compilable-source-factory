<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
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
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        DomIdentifier $identifier,
        bool $asCollection,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($identifier, $asCollection);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleDataProvider(): array
    {
        return [
            'element, no parent' => [
                'identifier' => new DomIdentifier('.selector'),
                'asCollection' => false,
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
            'element, has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'asCollection' => false,
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
            'identifier, no parent' => [
                'identifier' => new DomIdentifier('.selector'),
                'asCollection' => true,
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
            'identifier, has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'asCollection' => true,
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
            'element value, no parent' => [
                'identifier' => new DomIdentifier('.selector'),
                'asCollection' => true,
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
            'element value, has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'asCollection' => true,
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = '
                    . '{{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
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
            'attribute value, no parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withAttributeName('attribute_name'),
                'asCollection' => false,
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
            'attribute value, has parent' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withAttributeName('attribute_name')
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'asCollection' => false,
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}'
                    . '->hasOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
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
