<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomIdentifierValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');

        $namedDomElementIdentifier = new NamedDomIdentifierValue($identifier);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertTrue($namedDomElementIdentifier->includeValue());
    }

    public function testAsCollection()
    {
        $elementValue = new NamedDomIdentifierValue(
            new ElementIdentifier('.selector')
        );
        $attributeValue = new NamedDomIdentifierValue(
            new AttributeIdentifier('.selector', 'attribute_name')
        );

        $this->assertTrue($elementValue->asCollection());
        $this->assertFalse($attributeValue->asCollection());
    }
}
