<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementIdentifierCallFactory
     */
    private $factory;

    /**
     * @var TestCodeGenerator
     */
    private $testCodeGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementIdentifierCallFactory::createFactory();
        $this->testCodeGenerator = TestCodeGenerator::create();
    }

    /**
     * @dataProvider createConstructorCallDataProvider
     */
    public function testCreateConstructorCall(
        ElementIdentifierInterface $elementIdentifier,
        ElementIdentifierInterface $expectedElementIdentifier
    ) {
        $constructorExpression = $this->factory->createConstructorCall($elementIdentifier);

        $this->assertEquals(
            new Metadata([
                Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                    new ClassDependency(ElementIdentifier::class),
                ])
            ]),
            $constructorExpression->getMetadata()
        );

        $constructorAccessStatement = new ReturnStatement($constructorExpression);

        $code = $constructorAccessStatement->getMetadata()->getClassDependencies()->render() .
            "\n" .
            $constructorAccessStatement->render();

        $evaluatedCodeOutput = eval($code);

        $this->assertEquals($expectedElementIdentifier, $evaluatedCodeOutput);
    }

    public function createConstructorCallDataProvider(): array
    {
        return [
            'css selector, no parent, no ordinal position' => [
                'elementIdentifier' => new ElementIdentifier('.selector'),
                'expectedElementIdentifier' => new ElementIdentifier('.selector'),
            ],
            'css selector, no parent, has ordinal position' => [
                'elementIdentifier' => new ElementIdentifier('.selector', 2),
                'expectedElementIdentifier' => new ElementIdentifier('.selector', 2),
            ],
            'css selector with attribute, no parent, no ordinal position' => [
                'elementIdentifier' => new AttributeIdentifier('.selector', 'attribute_name'),
                'expectedElementIdentifier' => new ElementIdentifier('.selector'),
            ],
            'css selector with attribute, no parent, has ordinal position' => [
                'elementIdentifier' => new AttributeIdentifier('.selector', 'attribute_name', 2),
                'expectedElementIdentifier' => new ElementIdentifier('.selector', 2),
            ],
            'css selector, has parent, no ordinal position' => [
                'elementIdentifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector, has parent, has ordinal position' => [
                'elementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector with attribute, has parent, no ordinal position' => [
                'elementIdentifier' => (new AttributeIdentifier('.selector', 'attribute_name'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector with attribute, has parent, has ordinal position' => [
                'elementIdentifier' => (new AttributeIdentifier('.selector', 'attribute_name', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css selector, has parent, has ordinal positions' => [
                'elementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent', 3)),
                'expectedElementIdentifier' => (new ElementIdentifier('.selector', 2))
                    ->withParentIdentifier(new ElementIdentifier('.parent', 3)),
            ],
            'css selector with attribute, ordinal position, parent and grandparent with attribute and position' => [
                'elementIdentifier' => (new AttributeIdentifier('.selector', 'attribute_name', 2))
                    ->withParentIdentifier(
                        (new AttributeIdentifier('.parent', 'parent_attribute_name', 3))
                            ->withParentIdentifier(
                                new AttributeIdentifier('.grandparent', 'grandparent_attribute_name', 4)
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
                'elementIdentifier' => new ElementIdentifier('input[name="email"]'),
                'expectedElementIdentifier' => new ElementIdentifier('input[name="email"]'),
            ],
            'css selector, single quotes in selector, no ordinal position' => [
                'elementIdentifier' => new ElementIdentifier("input[name='email']"),
                'expectedElementIdentifier' => new ElementIdentifier("input[name='email']"),
            ],
            'css selector, escaped single quotes in selector, no ordinal position' => [
                'elementIdentifier' => new ElementIdentifier("input[value='\'quoted\'']"),
                'expectedElementIdentifier' => new ElementIdentifier("input[value='\'quoted\'']"),
            ],
            'css selector, escaped single quotes within selector' => [
                'elementIdentifier' => new ElementIdentifier("input[value='va\'l\'ue']"),
                'expectedElementIdentifier' => new ElementIdentifier("input[value='va\'l\'ue']"),
            ],
            'css selector, escaped double quotes in selector, no ordinal position' => [
                'elementIdentifier' => new ElementIdentifier("input[value=\"'quoted'\"]"),
                'expectedElementIdentifier' => new ElementIdentifier("input[value=\"'quoted'\"]"),
            ],
        ];
    }
}
