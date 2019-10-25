<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Exception;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;

class NonTranspilableModelExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $model = new \stdClass();

        $exception = new UnsupportedModelException($model);

        $this->assertSame($model, $exception->getModel());
    }
}
