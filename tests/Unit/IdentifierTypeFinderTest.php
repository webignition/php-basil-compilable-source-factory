<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\IdentifierTypeFinder;

/**
 * @group poc208
 */
class IdentifierTypeFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider cssSelectorDataProvider
     */
    public function testIsCssSelector(string $identifier)
    {
        $this->assertTrue(IdentifierTypeFinder::isCssSelector($identifier));
    }

    /**
     * @dataProvider xPathExpressionDataProvider
     * @dataProvider attributeSelectorDataProvider
     * @dataProvider unknownTypeDataProvider
     * @dataProvider descendantDomIdentifierDataProvider
     */
    public function testIsNotCssSelector(string $identifier)
    {
        $this->assertFalse(IdentifierTypeFinder::isCssSelector($identifier));
    }

    /**
     * @dataProvider xPathExpressionDataProvider
     */
    public function testIsXpathExpression(string $identifier)
    {
        $this->assertTrue(IdentifierTypeFinder::isXpathExpression($identifier));
    }

    /**
     * @dataProvider cssSelectorDataProvider
     * @dataProvider attributeSelectorDataProvider
     * @dataProvider unknownTypeDataProvider
     * @dataProvider descendantDomIdentifierDataProvider
     */
    public function testIsNotXpathExpression(string $identifier)
    {
        $this->assertFalse(IdentifierTypeFinder::isXpathExpression($identifier));
    }

    /**
     * @dataProvider cssSelectorDataProvider
     * @dataProvider xPathExpressionDataProvider
     */
    public function testIsElementIdentifier(string $identifier)
    {
        $this->assertTrue(IdentifierTypeFinder::isElementIdentifier($identifier));
    }

    /**
     * @dataProvider attributeSelectorDataProvider
     * @dataProvider unknownTypeDataProvider
     * @dataProvider descendantDomIdentifierDataProvider
     */
    public function testIsNotElementIdentifier(string $identifier)
    {
        $this->assertFalse(IdentifierTypeFinder::isElementIdentifier($identifier));
    }

    /**
     * @dataProvider attributeSelectorDataProvider
     */
    public function testIsAttributeIdentifier(string $identifier)
    {
        $this->assertTrue(IdentifierTypeFinder::isAttributeIdentifier($identifier));
    }

    /**
     * @dataProvider cssSelectorDataProvider
     * @dataProvider xPathExpressionDataProvider
     * @dataProvider unknownTypeDataProvider
     * @dataProvider descendantDomIdentifierDataProvider
     */
    public function testIsNotAttributeIdentifier(string $identifier)
    {
        $this->assertFalse(IdentifierTypeFinder::isAttributeIdentifier($identifier));
    }

    /**
     * @dataProvider cssSelectorDataProvider
     * @dataProvider xPathExpressionDataProvider
     * @dataProvider attributeSelectorDataProvider
     */
    public function testIsDomIdentifier(string $identifier)
    {
        $this->assertTrue(IdentifierTypeFinder::isDomIdentifier($identifier));
    }

    /**
     * @dataProvider unknownTypeDataProvider
     * @dataProvider descendantDomIdentifierDataProvider
     */
    public function testIsNotDomIdentifier(string $identifier)
    {
        $this->assertFalse(IdentifierTypeFinder::isDomIdentifier($identifier));
    }

    /**
     * @dataProvider descendantDomIdentifierDataProvider
     */
    public function testIsDescendantDomIdentifierTest(string $identifier)
    {
        $this->assertTrue(IdentifierTypeFinder::isDescendantDomIdentifier($identifier));
    }


    public function cssSelectorDataProvider(): array
    {
        return [
            [
                'identifierString' =>  '$".selector"',
            ],
            [
                'identifierString' =>  '$".selector .foo"',
            ],
            [
                'identifierString' =>  '$".selector.foo"',
            ],
            [
                'identifierString' =>  '$"#id"',
            ],
            [
                'identifierString' =>  '$".selector[data-foo=bar]"',
            ],
            [
                'identifierString' =>  '$".selector":0',
            ],
            [
                'identifierString' =>  '$".selector":1',
            ],
            [
                'identifierString' =>  '$".selector":-1',
            ],
            [
                'identifierString' =>  '$".selector":first',
            ],
            [
                'identifierString' =>  '$".selector":last',
            ],
        ];
    }


    public function xPathExpressionDataProvider(): array
    {
        return [
            [
                'identifierString' =>  '$"/body"',
            ],
            [
                'identifierString' =>  '$"//foo"',
            ],
            [
                'identifierString' =>  '$"//*[@id="id"]"',
            ],
            [
                'identifierString' =>  '$"//hr[@class=\'edge\']"',
            ],
            [
                'identifierString' =>  '$"/body":0',
            ],
            [
                'identifierString' =>  '$"/body":1',
            ],
            [
                'identifierString' =>  '$"/body":-1',
            ],
            [
                'identifierString' =>  '$"/body":first',
            ],
            [
                'identifierString' =>  '$"/body":last',
            ],
        ];
    }

    public function attributeSelectorDataProvider(): array
    {
        return [
            [
                'identifierString' =>  '$".selector".attribute_name',
            ],
            [
                'identifierString' =>  '$".selector .foo".attribute_name',
            ],
            [
                'identifierString' =>  '$".selector.foo".attribute_name',
            ],
            [
                'identifierString' =>  '$"#id".attribute_name',
            ],
            [
                'identifierString' =>  '$".selector[data-foo=bar]".attribute_name',
            ],
            [
                'identifierString' =>  '$"/body".attribute_name',
            ],
            [
                'identifierString' =>  '$"//foo".attribute_name',
            ],
            [
                'identifierString' =>  '$"//*[@id="id"]".attribute_name',
            ],
            [
                'identifierString' =>  '$"//hr[@class=\'edge\']".attribute_name',
            ],
            [
                'identifierString' =>  '$".selector":0.attribute_name',
            ],
            [
                'identifierString' =>  '$".selector":1.attribute_name',
            ],
            [
                'identifierString' =>  '$".selector":-1.attribute_name',
            ],
            [
                'identifierString' =>  '$".selector":first.attribute_name',
            ],
            [
                'identifierString' =>  '$".selector":last.attribute_name',
            ],
        ];
    }

    public function unknownTypeDataProvider(): array
    {
        return  [
            'empty' => [
                'identifierString' => '',
            ],
            'literal value' => [
                'identifierString' => 'invalid',
            ],
            'quoted literal value' => [
                'identifierString' => '"invalid"',
            ],
            'element reference' => [
                'identifierString' =>  '$elements.element_name',
            ],
            'page element reference' => [
                'identifierString' => '$page_import_name.elements.element_name',
            ],
            'attribute reference' => [
                'identifierString' =>  '$elements.element_name.attribute_name',
            ],
        ];
    }

    public function descendantDomIdentifierDataProvider(): array
    {
        return [
            [
                'identifierString' =>  '{{ $".parent" }} $".selector"',
            ],
            [
                'identifierString' =>  '{{ $".parent" }} $"/body"',
            ],
            [
                'identifierString' =>  '{{ $".parent" }} $".selector".attribute_name',
            ],
            [
                'identifierString' =>  '{{ $".parent" }} $"/body".attribute_name',
            ],
            [
                'identifierString' =>  '{{ $"/parent" }} $".selector"',
            ],
            [
                'identifierString' =>  '{{ $"/parent" }} $"/body"',
            ],
            [
                'identifierString' =>  '{{ $"/parent" }} $".selector".attribute_name',
            ],
            [
                'identifierString' =>  '{{ $"/parent" }} $"/body".attribute_name',
            ],
        ];
    }
}
