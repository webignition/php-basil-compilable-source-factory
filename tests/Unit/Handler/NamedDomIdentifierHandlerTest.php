<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\DomElementLocator\ElementLocator;

class NamedDomIdentifierHandlerTest extends AbstractTestCase
{
    /**
     * @var NamedDomIdentifierHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = NamedDomIdentifierHandler::createHandler();
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(
        NamedDomIdentifierInterface $namedDomIdentifier,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($namedDomIdentifier);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'E',
                    ])),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new DomIdentifier('.selector'))
                        ->withParentIdentifier(new DomIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = '
                        . '{{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = '
                        . '{{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'E',
                    ])),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new DomIdentifier('.selector'))
                        ->withAttributeName('attribute_name'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
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
                        'E',
                    ])),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new DomIdentifier('.selector'))
                        ->withAttributeName('attribute_name')
                        ->withParentIdentifier(new DomIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}'
                    . '->hasOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ E }} = {{ NAVIGATOR }}'
                    . '->findOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
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
                        'E',
                    ])),
            ],
        ];
    }

    /**
     * @dataProvider handleAccessDataProvider
     */
    public function testHandleAccess(
        NamedDomIdentifierInterface $namedDomIdentifier,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handleAccess($namedDomIdentifier);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleAccessDataProvider(): array
    {
        return [
            'element, no parent' => [
                'value' => new NamedDomElementIdentifier(
                    new DomIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'element, has parent' => [
                'value' => new NamedDomElementIdentifier(
                    (new DomIdentifier('.selector'))
                        ->withParentIdentifier(new DomIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne('
                    . 'new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\')'
                    . ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'identifier, no parent' => [
                'value' => new NamedDomIdentifier(
                    new DomIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'identifier, has parent' => [
                'value' => new NamedDomIdentifier(
                    (new DomIdentifier('.selector'))
                        ->withParentIdentifier(new DomIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find('
                    . 'new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\')'
                    . ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'element value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new DomIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new DomIdentifier('.selector'))
                        ->withParentIdentifier(new DomIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = '
                    . '{{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new DomIdentifier('.selector'))
                        ->withAttributeName('attribute_name'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.selector\'))',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new DomIdentifier('.selector'))
                        ->withAttributeName('attribute_name')
                        ->withParentIdentifier(new DomIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}'
                    . '->findOne(new ElementLocator(\'.selector\'), new ElementLocator(\'.parent\'))',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'E',
                    ])),
            ],
        ];
    }
}
