<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;

class DataProviderMethodDefinitionTest extends AbstractResolvableTestCase
{
    /**
     * @param array<mixed> $data
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(string $name, array $data): void
    {
        $methodDefinition = new DataProviderMethodDefinition($name, $data);

        $this->assertSame($name, $methodDefinition->getName());
        $this->assertSame([], $methodDefinition->getArguments());
        $this->assertsame(MethodDefinition::VISIBILITY_PUBLIC, $methodDefinition->getVisibility());
        $this->assertSame('array', $methodDefinition->getReturnType());
        $this->assertSame($data, $methodDefinition->getData());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'empty data' => [
                'name' => 'emptyData',
                'data' => [],
            ],
            'non-empty data' => [
                'name' => 'nonEmptyData',
                'data' => [
                    0 => [
                        'x' => '1',
                        'y' => '\'string1\'',
                    ],
                    1 => [
                        'x' => '2',
                        'y' => '\'string2\'',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(DataProviderMethodDefinitionInterface $methodDefinition, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $methodDefinition);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'empty data' => [
                'methodDefinition' => new DataProviderMethodDefinition('emptyDataDataProvider', []),
                'expectedString' => 'public function emptyDataDataProvider(): array' . "\n"
                    . '{' . "\n"
                    . '    return [];' . "\n"
                    . '}'
            ],
            'non-empty data' => [
                'methodDefinition' => new DataProviderMethodDefinition('emptyDataDataProvider', [
                    0 => [
                        'x' => '1',
                        'y' => "\\'string1\\'",
                    ],
                    1 => [
                        'x' => '2',
                        'y' => "\\'string2\\'",
                    ],
                ]),
                'expectedString' => "public function emptyDataDataProvider(): array\n"
                    . "{\n"
                    . "    return [\n"
                    . "        '0' => [\n"
                    . "            'x' => '1',\n"
                    . "            'y' => '\\'string1\\'',\n"
                    . "        ],\n"
                    . "        '1' => [\n"
                    . "            'x' => '2',\n"
                    . "            'y' => '\\'string2\\'',\n"
                    . "        ],\n"
                    . "    ];\n"
                    . '}'
            ],
        ];
    }
}
