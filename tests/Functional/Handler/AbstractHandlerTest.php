<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;

abstract class AbstractHandlerTest extends AbstractBrowserTestCase
{
    /**
     * @var HandlerInterface
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->createHandler();
    }

    abstract protected function createHandler(): HandlerInterface;
}
