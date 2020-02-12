<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

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
        $this->markTestSkipped();

        $source = $this->handler->handle($namedDomIdentifier);

        $this->assertInstanceOf(CodeBlockInterface::class, $source);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleDataProvider(): array
    {
        return [
            'element, no parent' => [
                'value' => new NamedDomElementIdentifier(
                    new ElementIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    new ElementIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    new ElementIdentifier('.selector'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ')',
                    '{{ E }} = {{ INSPECTOR }}->getValue({{ E }})',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    new AttributeIdentifier('.selector', 'attribute_name'),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
                    (new AttributeIdentifier('.selector', 'attribute_name'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    new VariablePlaceholder('E')
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ E }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(' .
                    '\'{"locator":".selector","parent":{"locator":".parent"}}\'' .
                    ')' .
                    ')',
                    '{{ E }} = {{ E }}->getAttribute(\'attribute_name\')',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
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
