<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;

abstract class AbstractHandlerTest extends \PHPUnit\Framework\TestCase
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

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->handler->createStatementList($model);
    }

    public function testHandlesUnhandledModel()
    {
        $this->assertFalse($this->handler->handles(new \stdClass()));
    }
}
