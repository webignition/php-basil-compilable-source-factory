<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var ExecutableCallFactory
     */
    protected $executableCallFactory;

    protected function setUp(): void
    {
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }
}
