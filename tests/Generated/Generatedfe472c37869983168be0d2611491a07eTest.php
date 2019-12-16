<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generatedfe472c37869983168be0d2611491a07eTest extends AbstractGeneratedTestCase
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

    public function testd7cced626be1531a0324c1b596d5e793()
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('input[name=input-without-value]', 0));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-without-value]', 0));
        $value = "" ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);

        // Additional teardown statements
        $input = self::$crawler->filter('input[name=input-without-value]')->getElement(0);
        $this->assertSame("", $input->getAttribute("value"));
    }
}
