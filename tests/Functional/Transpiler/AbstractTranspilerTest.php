<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler;

use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;

abstract class AbstractTranspilerTest extends AbstractTestCase
{
    /**
     * @var TranspilerInterface
     */
    protected $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = $this->createTranspiler();
    }

    abstract protected function createTranspiler(): TranspilerInterface;
}
