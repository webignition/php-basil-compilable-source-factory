<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;

abstract class AbstractTranspilerTest extends AbstractBrowserTestCase
{
    /**
     * @var HandlerInterface
     */
    protected $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = $this->createTranspiler();
    }

    abstract protected function createTranspiler(): HandlerInterface;
}
