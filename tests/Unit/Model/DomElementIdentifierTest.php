<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\DomElementIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class DomElementIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');

        $domElementIdentifier = new DomElementIdentifier($identifier);

        $this->assertSame($identifier, $domElementIdentifier->getIdentifier());
        $this->assertFalse($domElementIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $elementIdentifier = new DomElementIdentifier(
            new ElementIdentifier('.selector')
        );

        $attributeIdentifier = new DomElementIdentifier(
            new AttributeIdentifier('.selector', 'attribute_name')
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
