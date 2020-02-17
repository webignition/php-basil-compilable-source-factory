<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifierValue;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class DomIdentifierValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');

        $domIdentifierValue = new DomIdentifierValue($identifier);

        $this->assertSame($identifier, $domIdentifierValue->getIdentifier());
        $this->assertTrue($domIdentifierValue->includeValue());
    }

    public function testAsCollection()
    {
        $elementValue = new DomIdentifierValue(
            new ElementIdentifier('.selector')
        );
        $attributeValue = new DomIdentifierValue(
            new AttributeIdentifier('.selector', 'attribute_name')
        );

        $this->assertTrue($elementValue->asCollection());
        $this->assertFalse($attributeValue->asCollection());
    }
}
