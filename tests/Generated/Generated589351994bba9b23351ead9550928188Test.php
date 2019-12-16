<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated589351994bba9b23351ead9550928188Test extends AbstractGeneratedTestCase
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

    public function testbc5e8d08bcbf11573980decc58335d31()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('.select-none-selected', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('.select-none-selected', 0));
        $value = "invalid" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $select = self::$crawler->filter('.select-none-selected')->getElement(0);
        $this->assertSame("none-selected-1", $select->getAttribute("value"));
    }
}
