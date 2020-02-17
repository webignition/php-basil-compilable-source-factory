<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');

        $namedDomElementIdentifier = new NamedDomIdentifier($identifier);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertTrue($namedDomElementIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $elementIdentifier = new NamedDomIdentifier(
            new ElementIdentifier('.selector')
        );
        $attributeIdentifier = new NamedDomIdentifier(
            new AttributeIdentifier('.selector', 'attribute_name')
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
