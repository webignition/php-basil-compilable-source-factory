<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomIdentifierValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $this->markTestSkipped();

        $identifier = new ElementIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomIdentifierValue($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertTrue($namedDomElementIdentifier->includeValue());
    }

    public function testAsCollection()
    {
        $this->markTestSkipped();

        $identifier = new ElementIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $elementValue = new NamedDomIdentifierValue(
            new ElementIdentifier('.selector'),
            $placeholder
        );
        $attributeValue = new NamedDomIdentifierValue(
            new AttributeIdentifier('.selector', 'attribute_name'),
            $placeholder
        );

        $this->assertTrue($elementValue->asCollection());
        $this->assertFalse($attributeValue->asCollection());
    }
}
