<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class DomIdentifierHandlerTest extends AbstractResolvableTestCase
{
    private DomIdentifierHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = DomIdentifierHandler::createHandler();
    }

    /**
     * @dataProvider handleElementDataProvider
     */
    public function testHandleElement(
        string $serializedElementIdentifier,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $this->handler->handleElement($serializedElementIdentifier);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function handleElementDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'element, no parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('.selector')
                ),
                'expectedRenderedSource' => '{{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '    "locator": ".selector"' . "\n"
                    . '}\'))',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'element, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' => '{{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '    "locator": ".selector",' . "\n"
                    . '    "parent": {' . "\n"
                    . '        "locator": ".parent"' . "\n"
                    . '    }' . "\n"
                    . '}\'))',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider handleElementCollectionDataProvider
     */
    public function testHandleElementCollection(
        string $serializedElementIdentifier,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $this->handler->handleElementCollection($serializedElementIdentifier);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function handleElementCollectionDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'element, collection no parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('.selector')
                ),
                'expectedRenderedSource' => '{{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '    "locator": ".selector"' . "\n"
                    . '}\'))',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'element collection, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' => '{{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '    "locator": ".selector",' . "\n"
                    . '    "parent": {' . "\n"
                    . '        "locator": ".parent"' . "\n"
                    . '    }' . "\n"
                    . '}\'))',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider handleAttributeValueDataProvider
     */
    public function testHandleAttributeValue(
        string $serializedElementIdentifier,
        string $attributeName,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $this->handler->handleAttributeValue($serializedElementIdentifier, $attributeName);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function handleAttributeValueDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'attribute value, no parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new AttributeIdentifier('.selector', 'attribute_name')
                ),
                'attributeName' => 'attribute_name',
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})()',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'attribute value, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new AttributeIdentifier('.selector', 'attribute_name'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'attributeName' => 'attribute_name',
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent"' . "\n"
                    . '        }' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})()',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
        ];
    }

    /**
     * @dataProvider handleElementValueDataProvider
     */
    public function testHandleElementValue(
        string $serializedElementIdentifier,
        string $expectedRenderedSource,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $this->handler->handleElementValue($serializedElementIdentifier);

        $this->assertRenderResolvable($expectedRenderedSource, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function handleElementValueDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'element value, no parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('.selector')
                ),
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})()',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'element value, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent"' . "\n"
                    . '        }' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})()',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
        ];
    }
}
