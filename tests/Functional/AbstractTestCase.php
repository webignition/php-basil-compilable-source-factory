<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\CodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var ExecutableCallFactory
     */
    protected $executableCallFactory;

    /**
     * @var CodeGenerator
     */
    protected $codeGenerator;

    protected function setUp(): void
    {
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
        $this->codeGenerator = CodeGenerator::create();
    }
}
