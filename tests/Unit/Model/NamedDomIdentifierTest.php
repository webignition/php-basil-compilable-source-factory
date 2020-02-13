<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;

class NamedDomIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new ElementIdentifier('.selector');
        $placeholder =  VariablePlaceholder::createExport('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomIdentifier($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertTrue($namedDomElementIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $placeholder =  VariablePlaceholder::createExport('PLACEHOLDER');

        $elementIdentifier = new NamedDomIdentifier(
            new ElementIdentifier('.selector'),
            $placeholder
        );
        $attributeIdentifier = new NamedDomIdentifier(
            new AttributeIdentifier('.selector', 'attribute_name'),
            $placeholder
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
