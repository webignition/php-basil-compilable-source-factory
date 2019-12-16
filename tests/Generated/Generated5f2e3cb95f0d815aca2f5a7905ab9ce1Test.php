<?php

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\DomElementLocator\ElementLocator;

class Generated5f2e3cb95f0d815aca2f5a7905ab9ce1Test extends AbstractGeneratedTestCase
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
        $submitButton = self::$crawler->filter('#form input[type="submit"]')->getElement(0);
        $this->assertSame("false", $submitButton->getAttribute('data-clicked'));
    }

    public function test9314591025a3f5298d885f82dfbfb694()
    {
        // Code under test
        $has = $this->navigator->hasOne(new ElementLocator('#form input[type=\'submit\']', 0));
        $this->assertTrue($has);
        $element = $this->navigator->findOne(new ElementLocator('#form input[type=\'submit\']', 0));
        $element->click();

        // Additional teardown statements
        $submitButton = self::$crawler->filter('#form input[type="submit"]')->getElement(0);
        $this->assertSame("true", $submitButton->getAttribute('data-clicked'));
    }
}
