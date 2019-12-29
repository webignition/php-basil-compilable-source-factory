<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilDomIdentifier\DomIdentifier;

class NamedDomIdentifierValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new DomIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomIdentifierValue($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertTrue($namedDomElementIdentifier->includeValue());
    }

    public function testAsCollection()
    {
        $identifier = new DomIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $elementValue = new NamedDomIdentifierValue($identifier, $placeholder);
        $attributeValue = new NamedDomIdentifierValue(
            $identifier->withAttributeName('attribute_name'),
            $placeholder
        );

        $this->assertTrue($elementValue->asCollection());
        $this->assertFalse($attributeValue->asCollection());
    }
}
