<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\InteractionActionHandler;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Parser\ActionParser;

class InteractionActionHandlerTest extends TestCase
{
    #[DataProvider('handleThrowsExceptionDataProvider')]
    public function testHandleThrowsException(
        ActionInterface $action,
        \Exception $expectedException
    ): void {
        $handler = InteractionActionHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('click $elements.element_name', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$elements.element_name'
                ),
            ],
            'attribute identifier' => [
                'action' => $actionParser->parse('submit $".selector".attribute_name', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector".attribute_name'
                ),
            ],
        ];
    }
}
