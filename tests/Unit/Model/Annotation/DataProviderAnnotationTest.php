<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Annotation;

use webignition\BasilCompilableSourceFactory\Model\Annotation\DataProviderAnnotation;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class DataProviderAnnotationTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(DataProviderAnnotation $annotation, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $annotation);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'default' => [
                'annotation' => new DataProviderAnnotation('dataProviderMethodName'),
                'expectedString' => '@dataProvider dataProviderMethodName',
            ],
        ];
    }
}
