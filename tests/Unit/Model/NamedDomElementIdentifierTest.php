<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomElementIdentifier;
use webignition\BasilCompilationSource\VariablePlaceholder;

class NamedDomElementIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate(): void
    {
        $identifier = new DomIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomElementIdentifier($identifier, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertFalse($namedDomElementIdentifier->asCollection());
    }

    public function testIncludeValue(): void
    {
        $placeholder = new VariablePlaceholder('PLACEHOLDER');
        $identifier = new DomIdentifier('.selector');


        $elementIdentifier = new NamedDomElementIdentifier($identifier, $placeholder);
        $attributeIdentifier = new NamedDomElementIdentifier(
            $identifier->withAttributeName('attribute_name'),
            $placeholder
        );

        $this->assertFalse($elementIdentifier->includeValue());
        $this->assertTrue($attributeIdentifier->includeValue());
    }
}
