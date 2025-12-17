<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
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
                'expectedRenderedSource' => <<< 'EOD'
                    {{ NAVIGATOR }}->findOne('{
                        "locator": ".selector"
                    }')
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'element, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' => <<< 'EOD'
                    {{ NAVIGATOR }}->findOne('{
                        "locator": ".selector",
                        "parent": {
                            "locator": ".parent"
                        }
                    }')
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
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
                'expectedRenderedSource' => <<< 'EOD'
                    {{ NAVIGATOR }}->find('{
                        "locator": ".selector"
                    }')
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'element collection, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' => <<< 'EOD'
                    {{ NAVIGATOR }}->find('{
                        "locator": ".selector",
                        "parent": {
                            "locator": ".parent"
                        }
                    }')
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
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
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector"
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })()
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'attribute value, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new AttributeIdentifier('.selector', 'attribute_name'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'attributeName' => 'attribute_name',
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->findOne('{
                            "locator": ".selector",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');
                    
                        return $element->getAttribute('attribute_name');
                    })()
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
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
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector"
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })()
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'element value, has parent' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedRenderedSource' => <<< 'EOD'
                    (function () {
                        $element = {{ NAVIGATOR }}->find('{
                            "locator": ".selector",
                            "parent": {
                                "locator": ".parent"
                            }
                        }');
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })()
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
        ];
    }
}
