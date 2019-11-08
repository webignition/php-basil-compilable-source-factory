<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;

abstract class AbstractHandlerTest extends AbstractTestCase
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

    public function testHandleForUnsupportedModel()
    {
        $this->expectException(UnsupportedModelException::class);
        $this->expectExceptionMessage('Unsupported model "stdClass"');

        $model = new \stdClass();

        $this->handler->handle($model);
    }

    public function testHandlesUnhandledModel()
    {
        $this->assertFalse($this->handler->handles(new \stdClass()));
    }
}
