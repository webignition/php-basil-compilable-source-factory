<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value;

use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;

trait DataParameterValueDataProviderTrait
{
    public function dataParameterValueDataProvider(): array
    {
        return [
            'default data parameter' => [
                'model' => new ObjectValue(
                    ObjectValueType::DATA_PARAMETER,
                    '$data.key',
                    'key'
                ),
            ],
        ];
    }
}
