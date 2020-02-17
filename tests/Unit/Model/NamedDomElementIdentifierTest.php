<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomElementIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');

        $namedDomElementIdentifier = new NamedDomElementIdentifier($identifier);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertFalse($namedDomElementIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $elementIdentifier = new NamedDomElementIdentifier(
            new ElementIdentifier('.selector')
        );

        $attributeIdentifier = new NamedDomElementIdentifier(
            new AttributeIdentifier('.selector', 'attribute_name')
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
