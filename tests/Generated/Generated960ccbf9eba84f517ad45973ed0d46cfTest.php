<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated960ccbf9eba84f517ad45973ed0d46cfTest extends AbstractGeneratedTestCase
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

    public function test6c3a6e3b0100919d0e4315c945abf758()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('input[name=input-with-value]', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-with-value]', 0));
        $value = "" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $input = self::$crawler->filter('input[name=input-with-value]')->getElement(0);
        $this->assertSame("", $input->getAttribute("value"));
    }
}
