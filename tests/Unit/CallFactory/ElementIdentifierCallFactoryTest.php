<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvableRenderer;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    private ElementIdentifierCallFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementIdentifierCallFactory::createFactory();
    }

    /**
     * @dataProvider createConstructorCallDataProvider
     */
    public function testCreateConstructorCall(
        string $serializedElementIdentifier,
        ElementIdentifierInterface $expectedElementIdentifier
    ): void {
        $constructorExpression = $this->factory->createConstructorCall(
            $serializedElementIdentifier
        );

        $this->assertEquals(
            new Metadata([
                Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                    new ClassName(ElementIdentifier::class),
                ])
            ]),
            $constructorExpression->getMetadata()
        );

        $constructorAccessStatement = new Statement(
            new ReturnExpression($constructorExpression)
        );

        $code = sprintf(
            "%s\n%s",
            ResolvableRenderer::resolve($constructorAccessStatement->getMetadata()->getClassDependencies()),
            ResolvableRenderer::resolve($constructorAccessStatement)
        );

        $evaluatedCodeOutput = eval($code);

        $this->assertEquals($expectedElementIdentifier, $evaluatedCodeOutput);
    }

    /**
     * @return array<mixed>
     */
    public function createConstructorCallDataProvider(): array
    {
        $elementIdentifierSerializer = ElementIdentifierSerializer::createSerializer();

        return [
            'css selector, no parent, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('.selector')
                ),
                'expectedElementIdentifier' => new ElementIdentifier('.selector'),
            ],
            'css selector, no parent, has ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('.selector', 2)
                ),
                'expectedElementIdentifier' => new ElementIdentifier('.selector', 2),
            ],
            'css selector with attribute, no parent, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new AttributeIdentifier('.selector', 'attribute_name')
                ),
                'expectedElementIdentifier' => new ElementIdentifier('.selector'),
            ],
            'css selector with attribute, no parent, has ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new AttributeIdentifier('.selector', 'attribute_name', 2)
                ),
                'expectedElementIdentifier' => new ElementIdentifier('.selector', 2),
            ],
            'css selector, has parent, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector, has parent, has ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector', 2))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector with attribute, has parent, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new AttributeIdentifier('.selector', 'attribute_name'))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector with attribute, has parent, has ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new AttributeIdentifier('.selector', 'attribute_name', 2))
                        ->withParentIdentifier(new ElementIdentifier('.parent'))
                ),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector, has parent, has ordinal positions' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new ElementIdentifier('.selector', 2))
                        ->withParentIdentifier(new ElementIdentifier('.parent', 3))
                ),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent', 3)),
            ],
            'css selector with attribute, ordinal position, parent and grandparent with attribute and position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    (new AttributeIdentifier('.selector', 'attribute_name', 2))
                        ->withParentIdentifier(
                            (new AttributeIdentifier('.parent', 'parent_attribute_name', 3))
                                ->withParentIdentifier(
                                    new AttributeIdentifier('.grandparent', 'grandparent_attribute_name', 4)
                                )
                        )
                ),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent', 3))
                            ->withParentIdentifier(
                                new ElementIdentifier('.grandparent', 4)
                            )
                    ),
            ],
            'css selector, double quotes in selector, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier('input[name="email"]')
                ),
                'expectedElementIdentifier' => new ElementIdentifier('input[name="email"]'),
            ],
            'css selector, single quotes in selector, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier("input[name='email']")
                ),
                'expectedElementIdentifier' => new ElementIdentifier("input[name='email']"),
            ],
            'css selector, escaped single quotes in selector, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier("input[value='\\'quoted\\'']")
                ),
                'expectedElementIdentifier' => new ElementIdentifier("input[value='\\'quoted\\'']"),
            ],
            'css selector, escaped single quotes within selector' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier("input[value='va\\'l\\'ue']")
                ),
                'expectedElementIdentifier' => new ElementIdentifier("input[value='va\\'l\\'ue']"),
            ],
            'css selector, escaped double quotes in selector, no ordinal position' => [
                'serializedElementIdentifier' => $elementIdentifierSerializer->serialize(
                    new ElementIdentifier("input[value=\"'quoted'\"]")
                ),
                'expectedElementIdentifier' => new ElementIdentifier("input[value=\"'quoted'\"]"),
            ],
        ];
    }
}
