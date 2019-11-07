<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use webignition\BasilCompilableSourceFactory\Tests\Services\CodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunner;
use webignition\BasePantherTestCase\AbstractBrowserTestCase as BaseAbstractBrowserTestCase;

abstract class AbstractBrowserTestCase extends BaseAbstractBrowserTestCase
{
    const FIXTURES_RELATIVE_PATH = '/Fixtures';
    const FIXTURES_HTML_RELATIVE_PATH = '/html';

    /**
     * @var CodeGenerator
     */
    protected $codeGenerator;

    /**
     * @var TestRunner
     */
    protected $testRunner;

    /**
     * @var TestCodeGenerator
     */
    protected $testCodeGenerator;

    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = __DIR__
            . '/..'
            . self::FIXTURES_RELATIVE_PATH
            . self::FIXTURES_HTML_RELATIVE_PATH;

        parent::setUpBeforeClass();
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));
    }

    protected function setUp(): void
    {
        $this->codeGenerator = CodeGenerator::create();
        $this->testRunner = new TestRunner();
        $this->testCodeGenerator = TestCodeGenerator::create();

        self::stopWebServer();
        self::$client->quit();
    }
}
