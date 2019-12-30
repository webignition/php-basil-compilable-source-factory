<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\DomElementIdentifier\DomIdentifier;

class NamedDomIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new DomIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomIdentifier($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertTrue($namedDomElementIdentifier->asCollection());
    }

    public function testIncludeValue()
    {
        $placeholder = new VariablePlaceholder('PLACEHOLDER');
        $identifier = new DomIdentifier('.selector');

        $elementIdentifier = new NamedDomIdentifier($identifier, $placeholder);
        $attributeIdentifier = new NamedDomIdentifier(
            $identifier->withAttributeName('attribute_name'),
            $placeholder
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
