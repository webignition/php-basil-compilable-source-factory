<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitForActionHandler;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\ObjectReflector\ObjectReflector;

class WaitForActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        ActionInterface $action,
        \Exception $expectedException,
        ?callable $initializer = null
    ): void {
        $handler = WaitForActionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    /**
     * @return array<mixed>
     */
    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('wait-for $elements.element_name'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$elements.element_name'
                ),
            ],
            'identifier is attribute reference' => [
                'action' => $actionParser->parse('wait-for $".selector".attribute_name'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector".attribute_name'
                ),
            ],
            'identifier cannot be extracted' => [
                'action' => $actionParser->parse('wait-for $".selector"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector"'
                ),
                'initializer' => function (WaitForActionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".selector"')
                        ->andReturnNull()
                    ;

                    ObjectReflector::setProperty(
                        $handler,
                        WaitForActionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }
}
