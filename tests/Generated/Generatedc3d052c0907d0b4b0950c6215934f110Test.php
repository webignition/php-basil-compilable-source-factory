<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generatedc3d052c0907d0b4b0950c6215934f110Test extends AbstractGeneratedTestCase
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
        $input = self::$crawler->filter('input[name=input-without-value]')->getElement(0);
        $this->assertSame("", $input->getAttribute("value"));
    }

    public function test84e3a7d422da65c565f144d685191608()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('input[name=input-without-value]', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-without-value]', 0));
        $value = "non-empty value" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $input = self::$crawler->filter('input[name=input-without-value]')->getElement(0);
        $this->assertSame("non-empty value", $input->getAttribute("value"));
    }
}
