<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Action\SetActionHandler;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\PageElementReference;

class SetActionHandlerTest extends AbstractTestCase
{
    public function testCreatesSourceForUnsupportedValue()
    {
        $handler = SetActionHandler::createHandler();

        $action = new InputAction(
            'set ".selector" to "foo"',
            new DomIdentifier('.selector'),
            new PageElementReference(
                'page_import_name.elements.element_name',
                'page_import_name',
                'element_name'
            ),
            '".selector" to "foo"'
        );

        $this->expectException(UnsupportedModelException::class);

        $handler->handle($action);
    }
}
