<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated87fb3fbaa22e53e53dc876dec000a2e9Test extends AbstractGeneratedTestCase
{
    public static function setUpBeforeClass() : void
    {
        // Test harness lines
        parent::setUpBeforeClass();
        self::$client->request('GET', Options::getBaseUri() . '/form.html');
    }

    protected  function setUp() : void
    {
        // Test harness lines
        parent::setUp();
        
        // Additional setup statements
        $input = self::$crawler->filter('input[name=input-without-value]')->getElement(0);
        $this->assertSame("", $input->getAttribute("value"));
    }

    public  function test0960e9b5b2a937ef78a416e749df0832() 
    {
        // Code under test
        $has = $this->navigator->has(new ElementLocator('input[name=input-without-value]'));
        $this->assertTrue($has);
        $collection = $this->navigator->find(new ElementLocator('input[name=input-without-value]'));
        $has = $this->navigator->hasOne(new ElementLocator('#form1'));
        $this->assertTrue($has);
        $value = $this->navigator->findOne(new ElementLocator('#form1'));
        $value = $value->getAttribute('action') ?? null;
        $value = (string) $value;
        self::$mutator->setValue($collection, $value);
        
        // Additional teardown statements
        $this->assertSame("http://127.0.0.1:9080/action1", $input->getAttribute("value"));
    }
}
