<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class DomIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');

        $domIdentifier = new DomIdentifier($identifier);

        $this->assertSame($identifier, $domIdentifier->getIdentifier());
        $this->assertTrue($domIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $elementIdentifier = new DomIdentifier(
            new ElementIdentifier('.selector')
        );
        $attributeIdentifier = new DomIdentifier(
            new AttributeIdentifier('.selector', 'attribute_name')
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
