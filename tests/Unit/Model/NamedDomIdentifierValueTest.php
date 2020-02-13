<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomIdentifierValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');
        $placeholder =  VariablePlaceholder::createExport('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomIdentifierValue($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertTrue($namedDomElementIdentifier->includeValue());
    }

    public function testAsCollection()
    {
        $placeholder =  VariablePlaceholder::createExport('PLACEHOLDER');

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
