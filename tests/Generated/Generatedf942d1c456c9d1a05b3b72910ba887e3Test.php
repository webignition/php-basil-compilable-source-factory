<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generatedf942d1c456c9d1a05b3b72910ba887e3Test extends AbstractGeneratedTestCase
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

    public function testde8814d6bf9742879074d6ef4b6d64de()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('.textarea-non-empty', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('.textarea-non-empty', 0));
        $value = "" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $textarea = self::$crawler->filter('.textarea-non-empty')->getElement(0);
        $this->assertSame("", $textarea->getAttribute("value"));
    }
}
