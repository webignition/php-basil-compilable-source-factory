<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Exception;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;

class UnknownObjectPropertyExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetValue()
    {
        $value = new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.foo', 'foo');

        $exception = new UnknownObjectPropertyException($value);

        $this->assertSame($value, $exception->getValue());
    }
}
