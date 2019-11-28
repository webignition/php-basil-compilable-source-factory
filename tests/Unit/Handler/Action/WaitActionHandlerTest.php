<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedActionException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilDataStructure\Action\ActionInterface;
use webignition\BasilParser\ActionParser;

/**
 * @group poc208
 */
class WaitActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider createForUnsupportedActionDataProvider
     */
    public function testCreateForUnsupportedActon(ActionInterface $action, string $expectedException)
    {
        $handler = WaitActionHandler::createHandler();

        $this->expectException($expectedException);

        $handler->handle($action);
    }

    public function createForUnsupportedActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'value is element reference' => [
                'action' => $actionParser->parse('wait $elements.element_name'),
                'expectedException' => UnsupportedValueException::class,
            ],
            'value is null' => [
                'action' => $actionParser->parse('wait'),
                'expectedException' => UnsupportedActionException::class,
            ],
        ];
    }
}
