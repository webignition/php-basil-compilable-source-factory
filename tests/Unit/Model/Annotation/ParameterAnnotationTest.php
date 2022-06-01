<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Annotation;

use webignition\BasilCompilableSourceFactory\Model\Annotation\ParameterAnnotation;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTest;

class ParameterAnnotationTest extends AbstractResolvableTest
{
    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(ParameterAnnotation $annotation, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $annotation);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'default' => [
                'annotation' => new ParameterAnnotation('string', new VariableName('name')),
                'expectedString' => '@param string $name',
            ],
        ];
    }
}
