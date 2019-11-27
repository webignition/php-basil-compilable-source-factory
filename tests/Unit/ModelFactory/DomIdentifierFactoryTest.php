<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\ModelFactory;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifierFactory;

class DomIdentifierFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DomIdentifierFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomIdentifierFactory::createFactory();
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider cssSelectorIdentifierDataProvider
     * @dataProvider xpathExpressionIdentifierDataProvider
     */
    public function testCreateSuccess(string $identifierString, DomIdentifier $expectedIdentifier)
    {
        $identifier = $this->factory->create($identifierString);

        $this->assertInstanceOf(DomIdentifier::class, $identifier);
        $this->assertEquals($expectedIdentifier, $identifier);
    }

    public function testCreateReturnsNull()
    {
        $this->assertNull($this->factory->create(''));
        $this->assertNull($this->factory->create('$elements.element_name'));
        $this->assertNull($this->factory->create('$page_import_name.elements.element_name'));
    }

    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier: css class selector, position: null' => [
                'identifierString' => '$".listed-item".attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item'))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', -1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: first' => [
                'identifierString' => '$".listed-item":first.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: last' => [
                'identifierString' => '$".listed-item":last.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', -1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath id selector' => [
                'identifierString' => '$"//*[@id="element-id"]".attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//*[@id="element-id"]'))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector, position: null' => [
                'identifierString' => '$"//input[@type="submit"]".attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]'))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: 1' => [
                'identifierString' => '$"//input[@type="submit"]":1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: -1' => [
                'identifierString' => '$"//input[@type="submit"]":-1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', -1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: first' => [
                'identifierString' => '$"//input[@type="submit"]":first.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: last' => [
                'identifierString' => '$"//input[@type="submit"]":last.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', -1))
                    ->withAttributeName('attribute_name'),
            ],
        ];
    }

    public function cssSelectorIdentifierDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '$"#element-id"',
                'expectedIdentifier' => new DomIdentifier('#element-id'),
            ],
            'css class selector, position: null' => [
                'identifierString' => '$".listed-item"',
                'expectedIdentifier' => new DomIdentifier('.listed-item'),
            ],
            'css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 1),
            ],
            'css class selector; position: 3' => [
                'identifierString' => '$".listed-item":3',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 3),
            ],
            'css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -1),
            ],
            'css class selector; position: -3' => [
                'identifierString' => '$".listed-item":-3',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -3),
            ],
            'css class selector; position: first' => [
                'identifierString' => '$".listed-item":first',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 1),
            ],
            'css class selector; position: last' => [
                'identifierString' => '$".listed-item":last',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -1),
            ],
        ];
    }

    public function xpathExpressionIdentifierDataProvider(): array
    {
        return [
            'xpath id selector' => [
                'identifierString' => '$"//*[@id=\"element-id\"]"',
                'expectedIdentifier' => new DomIdentifier('//*[@id="element-id"]'),
            ],
            'xpath attribute selector, position: null' => [
                'identifierString' => '$"//input[@type=\"submit\"]"',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]'),
            ],
            'xpath attribute selector; position: 1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":1',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', 1),
            ],
            'xpath attribute selector; position: 3' => [
                'identifierString' => '$"//input[@type=\"submit\"]":3',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', 3),
            ],
            'xpath attribute selector; position: -1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-1',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', -1),
            ],
            'xpath attribute selector; position: -3' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-3',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', -3),
            ],
            'xpath attribute selector; position: first' => [
                'identifierString' => '$"//input[@type=\"submit\"]":first',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', 1),
            ],
            'xpath attribute selector; position: last' => [
                'identifierString' => '$"//input[@type=\"submit\"]":last',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', -1),
            ],
        ];
    }
}
