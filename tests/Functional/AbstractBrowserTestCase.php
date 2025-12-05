<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunner;

abstract class AbstractBrowserTestCase extends TestCase
{
    protected TestRunner $testRunner;
    protected TestCodeGenerator $testCodeGenerator;

    protected function setUp(): void
    {
        $this->testRunner = new TestRunner();
        $this->testCodeGenerator = TestCodeGenerator::create();
    }
}
