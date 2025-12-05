<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableName;

class VariableNameTest extends AbstractResolvableTestCase
{
    public function testGetMetadata(): void
    {
        $this->assertEquals(new Metadata(), (new VariableName('name'))->getMetadata());
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(VariableName $placeholder, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $placeholder);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'empty' => [
                'placeholder' => new VariableName(''),
                'expectedString' => '$',
            ],
            'non-empty' => [
                'placeholder' => new VariableName('name'),
                'expectedString' => '$name',
            ],
        ];
    }
}
