<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated5bff44042062207cd6cd5a3e1a39a098Test extends AbstractGeneratedTestCase
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
        $textarea = self::$crawler->filter('.textarea-non-empty')->getElement(0);
        $this->assertSame("textarea content", $textarea->getAttribute("value"));
    }

    public function test089f6ce2681476dfb95c66fc195ee13c()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('.textarea-non-empty', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('.textarea-non-empty', 0));
        $value = "new value" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $textarea = self::$crawler->filter('.textarea-non-empty')->getElement(0);
        $this->assertSame("new value", $textarea->getAttribute("value"));
    }
}
