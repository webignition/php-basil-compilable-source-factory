<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider serializeDataProvider
     */
    public function testSerialize(
        ElementIdentifierInterface $elementIdentifier,
        int $indentDepth,
        string $expectedSerializedIdentifier
    ): void {
        $serializer = ElementIdentifierSerializer::createSerializer();

        $this->assertSame($expectedSerializedIdentifier, $serializer->serialize($elementIdentifier, $indentDepth));
    }

    /**
     * @return array<mixed>
     */
    public function serializeDataProvider(): array
    {
        return [
            'selector only, no indent' => [
                'elementIdentifier' => new ElementIdentifier('.selector'),
                'indentDepth' => 0,
                'expectedSerializedIdentifier' => '{' . "\n"
                    . '    "locator": ".selector"' . "\n"
                    . '}',
            ],
            'selector, position, no indent' => [
                'elementIdentifier' => new ElementIdentifier('.selector', 2),
                'indentDepth' => 0,
                'expectedSerializedIdentifier' => '{' . "\n"
                    . '    "locator": ".selector",' . "\n"
                    . '    "position": 2' . "\n"
                    . '}',
            ],
            'selector, position, parent, no indent' => [
                'elementIdentifier' => (
                    new ElementIdentifier('.child', 2))
                        ->withParentIdentifier(
                            new ElementIdentifier('.parent', 3)
                        ),
                'indentDepth' => 0,
                'expectedSerializedIdentifier' => '{' . "\n"
                    . '    "locator": ".child",' . "\n"
                    . '    "parent": {' . "\n"
                    . '        "locator": ".parent",' . "\n"
                    . '        "position": 3' . "\n"
                    . '    },' . "\n"
                    . '    "position": 2' . "\n"
                    . '}',
            ],
            'selector only, indent=1' => [
                'elementIdentifier' => new ElementIdentifier('.selector'),
                'indentDepth' => 1,
                'expectedSerializedIdentifier' => '    {' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }',
            ],
            'selector, position, indent=1' => [
                'elementIdentifier' => new ElementIdentifier('.selector', 2),
                'indentDepth' => 1,
                'expectedSerializedIdentifier' => '    {' . "\n"
                    . '        "locator": ".selector",' . "\n"
                    . '        "position": 2' . "\n"
                    . '    }',
            ],
            'selector, position, parent, indent=1' => [
                'elementIdentifier' => (
                new ElementIdentifier('.child', 2))
                    ->withParentIdentifier(
                        new ElementIdentifier('.parent', 3)
                    ),
                'indentDepth' => 1,
                'expectedSerializedIdentifier' => '    {' . "\n"
                    . '        "locator": ".child",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent",' . "\n"
                    . '            "position": 3' . "\n"
                    . '        },' . "\n"
                    . '        "position": 2' . "\n"
                    . '    }',
            ],
            'selector only, indent=2' => [
                'elementIdentifier' => new ElementIdentifier('.selector'),
                'indentDepth' => 2,
                'expectedSerializedIdentifier' => '        {' . "\n"
                    . '            "locator": ".selector"' . "\n"
                    . '        }',
            ],
        ];
    }
}
