<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomElementIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');
        $placeholder =  VariablePlaceholder::createExport('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomElementIdentifier($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertFalse($namedDomElementIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $placeholder =  VariablePlaceholder::createExport('PLACEHOLDER');

        $elementIdentifier = new NamedDomElementIdentifier(
            new ElementIdentifier('.selector'),
            $placeholder
        );

        $attributeIdentifier = new NamedDomElementIdentifier(
            new AttributeIdentifier('.selector', 'attribute_name'),
            $placeholder
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
