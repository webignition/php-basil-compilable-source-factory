<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated27cc51d70d4f44116f0738a03bfaad52Test extends AbstractGeneratedTestCase
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

    public function test6277684faf9c85043243ba9939c73730()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('.textarea-empty', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('.textarea-empty', 0));
        $value = "" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $textarea = self::$crawler->filter('.textarea-empty')->getElement(0);
        $this->assertSame("", $textarea->getAttribute("value"));
    }
}
