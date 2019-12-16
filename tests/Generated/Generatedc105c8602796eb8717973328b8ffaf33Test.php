<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generatedc105c8602796eb8717973328b8ffaf33Test extends AbstractGeneratedTestCase
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
        $select = self::$crawler->filter('.select-none-selected')->getElement(0);
        $this->assertSame("none-selected-1", $select->getAttribute("value"));
    }

    public function testb74e53229120ceee5bbecb7577bdd384()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('.select-none-selected', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('.select-none-selected', 0));
        $value = "none-selected-2" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $select = self::$crawler->filter('.select-none-selected')->getElement(0);
        $this->assertSame("none-selected-2", $select->getAttribute("value"));
    }
}
