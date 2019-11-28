<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
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
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(InputAction $action, \Exception $expectedException)
    {
        $handler = SetActionHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('set $elements.element_name to "value"'),
                'expectedException' => new UnsupportedIdentifierException('$elements.element_name'),
            ],
            'identifier is attribute reference' => [
                'action' => $actionParser->parse('set $".selector".attribute_name to "value"'),
                'expectedException' => new UnsupportedIdentifierException('$".selector".attribute_name'),
            ],
            'value is null' => [
                'action' => $actionParser->parse('set $".selector"'),
                'expectedException' => new UnsupportedValueException(null),
            ],
        ];
    }
}
