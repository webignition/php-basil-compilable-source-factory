<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Action\SetActionHandler;
use webignition\BasilDataStructure\Action\InputAction;
use webignition\BasilParser\ActionParser;

/**
 * @group poc208
 */
class SetActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider createForUnsupportedActionDataProvider
     */
    public function testCreateForUnsupportedActon(InputAction $action)
    {
        $handler = SetActionHandler::createHandler();

        $this->expectExceptionObject(new UnsupportedActionException($action));

        $handler->handle($action);
    }

    public function createForUnsupportedActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is attribute reference' => [
                'action' => $actionParser->parse('set $".selector".attribute_name to "value"')
            ],
            'value is null' => [
                'action' => $actionParser->parse('set $".selector"')
            ],
        ];
    }
}
