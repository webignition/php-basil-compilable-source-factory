<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Handler\Action\InteractionActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilModels\Action\InteractionActionInterface;
use webignition\BasilParser\ActionParser;

class InteractionActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        InteractionActionInterface $action,
        UnsupportedIdentifierException $expectedException
    ): void {
        $handler = InteractionActionHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('click $elements.element_name'),
                'expectedException' => new UnsupportedIdentifierException('$elements.element_name'),
            ],
            'attribute identifier' => [
                'action' => $actionParser->parse('submit $".selector".attribute_name'),
                'expectedException' => new UnsupportedIdentifierException('$".selector".attribute_name'),
            ],
        ];
    }
}
