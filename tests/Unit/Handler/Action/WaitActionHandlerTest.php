<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Value\PageElementReference;

class WaitActionHandlerTest extends AbstractTestCase
{
    public function testHandleForUnsupportedValue()
    {
        $handler = WaitActionHandler::createHandler();

        $action = new WaitAction(
            'wait 30',
            new PageElementReference(
                'page_import_name.elements.element_name',
                'page_import_name',
                'element_name'
            )
        );

        $this->expectException(UnsupportedModelException::class);

        $handler->handle($action);
    }
}
