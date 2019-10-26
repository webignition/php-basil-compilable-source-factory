<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Step\Step;

class StepHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return StepHandler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $this->assertTrue($this->handler->handles(new Step([], [])));
    }

    public function testCreateSource()
    {
        $step = new Step([], []);

        $source = $this->handler->createSource($step);

        $this->assertInstanceOf(SourceInterface::class, $source);
        $this->assertEquals([''], $source->getLines());
        $this->assertEquals(new Metadata(), $source->getMetadata());
    }
}
