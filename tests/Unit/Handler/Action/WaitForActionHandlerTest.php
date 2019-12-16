<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitForActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\BasilParser\ActionParser;

class WaitForActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(InteractionActionInterface $action, \Exception $expectedException)
    {
        $handler = WaitForActionHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('wait-for $elements.element_name'),
                'expectedException' => new UnsupportedIdentifierException('$elements.element_name'),
            ],
            'identifier is attribute reference' => [
                'action' => $actionParser->parse('wait-for $".selector".attribute_name'),
                'expectedException' => new UnsupportedIdentifierException('$".selector".attribute_name'),
            ],
        ];
    }
}
