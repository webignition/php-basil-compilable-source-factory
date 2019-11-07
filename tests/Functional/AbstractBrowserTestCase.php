<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunner;

abstract class AbstractBrowserTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestRunner
     */
    protected $testRunner;

    /**
     * @var TestCodeGenerator
     */
    protected $testCodeGenerator;

    protected function setUp(): void
    {
        $this->testRunner = new TestRunner();
        $this->testCodeGenerator = TestCodeGenerator::create();
    }
}
