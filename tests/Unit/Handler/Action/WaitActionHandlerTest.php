<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilDataStructure\Action\WaitAction;
use webignition\BasilParser\ActionParser;

/**
 * @group poc208
 */
class WaitActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider createForUnsupportedActionDataProvider
     */
    public function testCreateForUnsupportedActon(WaitAction $action, \Exception $expectedException)
    {
        $handler = WaitActionHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    public function createForUnsupportedActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'value is null' => [
                'action' => $actionParser->parse('wait'),
                'expectedException' => new UnsupportedValueException('')
            ],
        ];
    }
}
