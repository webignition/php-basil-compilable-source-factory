<?php

use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class GeneratedD894ed67e2008e18887400a33f7d82b3Test extends AbstractGeneratedTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$client->request('GET', 'http://127.0.0.1:9080/form.html');
        // Test harness addition for generating base test use statement;
    }

    public function test3107a74ec0409de9df51bfdeaae57bd1()
    {
        // verify form field values
        // $"input[name=input-without-value]" is ""
        $expectedValue = "" ?? null;
        $expectedValue = (string) $expectedValue;
        $has = $this->navigator->has(new ElementLocator('input[name=input-without-value]', 0));
        $this->assertTrue($has);
        $examinedValue = $this->navigator->find(new ElementLocator('input[name=input-without-value]', 0));
        $examinedValue = self::$inspector->getValue($examinedValue) ?? null;
        $examinedValue = (string) $examinedValue;
        $this->assertEquals($expectedValue, $examinedValue);

        // $"input[name=input-with-value]" is "test"
        $expectedValue = "test" ?? null;
        $expectedValue = (string) $expectedValue;
        $has = $this->navigator->has(new ElementLocator('input[name=input-with-value]', 0));
        $this->assertTrue($has);
        $examinedValue = $this->navigator->find(new ElementLocator('input[name=input-with-value]', 0));
        $examinedValue = self::$inspector->getValue($examinedValue) ?? null;
        $examinedValue = (string) $examinedValue;
        $this->assertEquals($expectedValue, $examinedValue);
    }

    /**
     * @dataProvider B290a482fafd2a27fd0914742c8074e2DataProvider
     */
    public function testB290a482fafd2a27fd0914742c8074e2($expected_value, $field_value)
    {
        // modify form field values
        // set $"input[name=input-without-value]" to $data.field_value
        $has = $this->navigator->has(new ElementLocator('input[name=input-without-value]', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-without-value]', 0));
        $value = $field_value ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // set $"input[name=input-with-value]" to $data.field_value
        $has = $this->navigator->has(new ElementLocator('input[name=input-with-value]', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-with-value]', 0));
        $value = $field_value ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // $"input[name=input-without-value]" is $data.expected_value
        $expectedValue = $expected_value ?? null;
        $expectedValue = (string) $expectedValue;
        $has = $this->navigator->has(new ElementLocator('input[name=input-without-value]', 0));
        $this->assertTrue($has);
        $examinedValue = $this->navigator->find(new ElementLocator('input[name=input-without-value]', 0));
        $examinedValue = self::$inspector->getValue($examinedValue) ?? null;
        $examinedValue = (string) $examinedValue;
        $this->assertEquals($expectedValue, $examinedValue);

        // $"input[name=input-with-value]" is $data.expected_value
        $expectedValue = $expected_value ?? null;
        $expectedValue = (string) $expectedValue;
        $has = $this->navigator->has(new ElementLocator('input[name=input-with-value]', 0));
        $this->assertTrue($has);
        $examinedValue = $this->navigator->find(new ElementLocator('input[name=input-with-value]', 0));
        $examinedValue = self::$inspector->getValue($examinedValue) ?? null;
        $examinedValue = (string) $examinedValue;
        $this->assertEquals($expectedValue, $examinedValue);
    }

    public function B290a482fafd2a27fd0914742c8074e2DataProvider()
    {
        return [
            '0' => [
                'expected_value' => 'value0',
                'field_value' => 'value0',
            ],
            '1' => [
                'expected_value' => 'value1',
                'field_value' => 'value1',
            ],
        ];
    }
}
