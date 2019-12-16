<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated6ea0d43a478e4314d1fee02ab51254f1Test extends AbstractGeneratedTestCase
{
    public static function setUpBeforeClass(): void
    {
        // Test harness lines
        parent::setUpBeforeClass();
        self::$client->request('GET', Options::getBaseUri() . '/form.html');
    }

    protected function setUp(): void
    {
        // Test harness lines
        parent::setUp();

        // Additional setup statements
        $input = self::$crawler->filter('input[name=input-with-value]')->getElement(0);
        $this->assertSame("test", $input->getAttribute("value"));
    }

    public function testb2d436a5595dd92b53b5a1d9aec4aa4a()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('input[name=input-with-value]', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-with-value]', 0));
        $value = "new value" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $input = self::$crawler->filter('input[name=input-with-value]')->getElement(0);
        $this->assertSame("new value", $input->getAttribute("value"));
    }
}
