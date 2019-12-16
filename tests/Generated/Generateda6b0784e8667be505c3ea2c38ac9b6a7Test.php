<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generateda6b0784e8667be505c3ea2c38ac9b6a7Test extends AbstractGeneratedTestCase
{
    public static function setUpBeforeClass(): void
    {
        // Test harness lines
        parent::setUpBeforeClass();
        self::$client->request('GET', Options::getBaseUri() . '/action-click-submit.html');
    }

    protected function setUp(): void
    {
        // Test harness lines
        parent::setUp();

        // Additional setup statements
        $this->assertSame("Click", self::$client->getTitle());
    }

    public function testdfa5156bf47a540f50236a7e13a1ea26()
    {
        // Code under test
        $has = $this->navigator->hasOne(new ElementLocator('#link-to-index', 0));
        $this->assertTrue($has);
        $element = $this->navigator->findOne(new ElementLocator('#link-to-index', 0));
        $element->click();

        // Additional teardown statements
        $this->assertSame("Test fixture web server default document", self::$client->getTitle());
    }
}
