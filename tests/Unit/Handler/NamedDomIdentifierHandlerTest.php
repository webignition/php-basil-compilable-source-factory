<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
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
        DomIdentifierInterface $namedDomIdentifier,
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
                    new ElementIdentifier('.selector')
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
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
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
                    new ElementIdentifier('.selector')
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
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
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
                    new ElementIdentifier('.selector')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                         ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
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
                        'ELEMENT',
                    ]),
                ]),
            ],
            'element value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                        ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
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
                        'ELEMENT',
                    ]),
                ]),
            ],
            'attribute value, no parent' => [
                'value' => new NamedDomIdentifierValue(
                    new AttributeIdentifier('.selector', 'attribute_name')
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                        ');' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
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
                        'ELEMENT',
                    ]),
                ]),
            ],
            'attribute value, has parent' => [
                'value' => new NamedDomIdentifierValue(
                    (new AttributeIdentifier('.selector', 'attribute_name'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                            'ElementIdentifier::fromJson(\'{"locator":".selector","parent":{"locator":".parent"}}\')' .
                        ');' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
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
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
