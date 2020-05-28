<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunner;

abstract class AbstractBrowserTestCase extends \PHPUnit\Framework\TestCase
{
    protected TestRunner $testRunner;
    protected TestCodeGenerator $testCodeGenerator;

    protected function setUp(): void
    {
        $this->testRunner = new TestRunner();
        $this->testCodeGenerator = TestCodeGenerator::create();
    }
}
