<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use webignition\BasilCompilableSourceFactory\Tests\Services\CodeGenerator;
use webignition\BasePantherTestCase\AbstractBrowserTestCase as BaseAbstractBrowserTestCase;

abstract class AbstractGeneratedTestCase extends BaseAbstractBrowserTestCase
{
    const FIXTURES_RELATIVE_PATH = '/Fixtures';
    const FIXTURES_HTML_RELATIVE_PATH = '/html';

    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = __DIR__
            . '/..'
            . self::FIXTURES_RELATIVE_PATH
            . self::FIXTURES_HTML_RELATIVE_PATH;

        parent::setUpBeforeClass();
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));
    }
}
