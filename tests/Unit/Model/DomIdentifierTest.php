<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;

class DomIdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testParentIdentifier()
    {
        $domIdentifier = new DomIdentifier('.selector');
        $this->assertNull($domIdentifier->getParentIdentifier());

        $parentIdentifier = new DomIdentifier('.parent');
        $domIdentifier = $domIdentifier->withParentIdentifier($parentIdentifier);

        $this->assertSame($parentIdentifier, $domIdentifier->getParentIdentifier());
    }

    public function testAttributeName()
    {
        $domIdentifier = new DomIdentifier('.selector');
        $this->assertNull($domIdentifier->getAttributeName());

        $attributeName = 'attribute_name';
        $domIdentifier = $domIdentifier->withAttributeName($attributeName);

        $this->assertSame($attributeName, $domIdentifier->getAttributeName());
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(DomIdentifier $domIdentifier, string $expectedString)
    {
        $this->assertSame($expectedString, (string) $domIdentifier);
    }

    public function toStringDataProvider(): array
    {
        return [
            'empty' => [
                'domIdentifier' => new DomIdentifier(''),
                'expectedString' => '$""',
            ],
            'css selector' => [
                'locator' => new DomIdentifier('.selector'),
                'expectedString' => '$".selector"',
            ],
            'css selector containing double quotes' => [
                'locator' => new DomIdentifier('a[href="https://example.org"]'),
                'expectedString' => '$"a[href=\"https://example.org\"]"',
            ],
            'xpath expression' => [
                'locator' => new DomIdentifier('//body'),
                'expectedString' => '$"//body"',
            ],
            'xpath expression containing double quotes' => [
                'locator' => new DomIdentifier('//*[@id="id"]'),
                'expectedString' => '$"//*[@id=\"id\"]"',
            ],
            'css selector with ordinal position' => [
                'locator' => new DomIdentifier('.selector', 3),
                'expectedString' => '$".selector":3',
            ],
            'css selector with attribute' => [
                'locator' => (new DomIdentifier('.selector'))
                    ->withAttributeName('attribute_name'),
                'expectedString' => '$".selector".attribute_name',
            ],
            'css selector with parent' => [
                'locator' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(
                        new DomIdentifier('.parent')
                    ),
                'expectedString' => '{{ $".parent" }} $".selector"',
            ],
            'css selector with parent, ordinal position and attribute name' => [
                'locator' => (new DomIdentifier('.selector', 7))
                    ->withAttributeName('attribute_name')
                    ->withParentIdentifier(
                        new DomIdentifier('.parent')
                    ),
                'expectedString' => '{{ $".parent" }} $".selector":7.attribute_name',
            ],
        ];
    }
}
