<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated4569e05ee30a807583ebb6566d5edde8Test extends AbstractGeneratedTestCase
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
        $textarea = self::$crawler->filter('.textarea-empty')->getElement(0);
        $this->assertSame("", $textarea->getAttribute("value"));
    }

    public function test2209f125fa2cf1d1d71d94424f613f58()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('.textarea-empty', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('.textarea-empty', 0));
        $value = "non-empty value" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $textarea = self::$crawler->filter('.textarea-empty')->getElement(0);
        $this->assertSame("non-empty value", $textarea->getAttribute("value"));
    }
}
