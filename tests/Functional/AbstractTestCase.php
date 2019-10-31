<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\CodeGenerator;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var CodeGenerator
     */
    protected $codeGenerator;

    protected function setUp(): void
    {
        $this->codeGenerator = CodeGenerator::create();
    }
}
