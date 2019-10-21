<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

class NamedDomIdentifierValueTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new DomIdentifier('.selector');
        $value = new DomIdentifierValue($identifier);
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $namedDomElementIdentifier = new NamedDomIdentifierValue($value, $placeholder);

        $this->assertSame($identifier, $namedDomElementIdentifier->getIdentifier());
        $this->assertSame($placeholder, $namedDomElementIdentifier->getPlaceholder());
        $this->assertTrue($namedDomElementIdentifier->includeValue());
        $this->assertFalse($namedDomElementIdentifier->isEmpty());
        $this->assertTrue($namedDomElementIdentifier->isActionable());
        $this->assertEquals('".selector"', (string) $namedDomElementIdentifier);
    }

    public function testAsCollection()
    {
        $identifier = new DomIdentifier('.selector');
        $placeholder = new VariablePlaceholder('PLACEHOLDER');

        $elementValue = new NamedDomIdentifierValue(new DomIdentifierValue($identifier), $placeholder);
        $attributeValue = new NamedDomIdentifierValue(
            new DomIdentifierValue($identifier->withAttributeName('attribute_name')),
            $placeholder
        );

        $this->assertTrue($elementValue->asCollection());
        $this->assertFalse($attributeValue->asCollection());
    }
}
