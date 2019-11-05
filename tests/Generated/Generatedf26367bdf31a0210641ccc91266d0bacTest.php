<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;

class Generatedf26367bdf31a0210641ccc91266d0bacTest extends AbstractGeneratedTestCase
{
    public static function setUpBeforeClass() : void
    {
        // Test harness lines
        parent::setUpBeforeClass();
        self::$client->request('GET', Options::getBaseUri() . '/action-wait-for.html');
    }

    protected  function setUp() : void
    {
        // Test harness lines
        parent::setUp();
        
        // Additional setup statements
        $this->assertCount(0, $crawler->filter("#hello"));
        usleep(100000);
        $this->assertCount(1, $crawler->filter("#hello"));
    }

    public  function test601ee78beafda0030e48065283e4a19d() 
    {
        // Code under test
        self::$crawler = self::$client->reload();
        
        // Additional teardown statements
        $this->assertCount(0, $crawler->filter("#hello"));
        usleep(100000);
        $this->assertCount(1, $crawler->filter("#hello"));
    }
}
