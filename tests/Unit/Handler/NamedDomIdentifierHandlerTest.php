<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
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
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ) {
        $source = $this->handler->handle($namedDomIdentifier);

        $this->assertSame($expectedRenderedSource, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleDataProvider(): array
    {
        return [
            'element, no parent' => [
                'value' => new NamedDomElementIdentifier(
                    new ElementIdentifier('.selector'),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    return {{ NAVIGATOR }}->findOne(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                        ');' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'element, has parent' => [
                'value' => new NamedDomElementIdentifier(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    return {{ NAVIGATOR }}->findOne(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                    ');' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'identifier, no parent' => [
                'value' => new NamedDomIdentifier(
                    new ElementIdentifier('.selector'),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    return {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                        ');' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'identifier, has parent' => [
                'value' => new NamedDomIdentifier(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    return {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                        ');' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'element value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new ElementIdentifier('.selector'),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ E }} = {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                         ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ E }});' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'E',
                    ]),
                ]),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ E }} = {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                        ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ E }});' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'E',
                    ]),
                ]),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new AttributeIdentifier('.selector', 'attribute_name'),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ E }} = {{ NAVIGATOR }}->findOne(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                        ');' . "\n" .
                    "\n" .
                    '    return {{ E }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'E',
                    ]),
                ]),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new AttributeIdentifier('.selector', 'attribute_name'))
                        ->withParentIdentifier(new ElementIdentifier('.parent')),
                    VariablePlaceholder::createExport('E')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ E }} = {{ NAVIGATOR }}->findOne(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                        ');' . "\n" .
                    "\n" .
                    '    return {{ E }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})()'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'E',
                    ]),
                ]),
            ],
        ];
    }
}
