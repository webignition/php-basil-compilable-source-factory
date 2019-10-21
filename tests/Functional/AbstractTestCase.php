<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;

abstract class AbstractTestCase extends PantherTestCase
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
