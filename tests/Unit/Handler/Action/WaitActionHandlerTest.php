<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\Action\SetActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Action\WaitActionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ObjectReflector;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilModels\Action\WaitActionInterface;
use webignition\BasilParser\ActionParser;

class WaitActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        WaitActionInterface $action,
        \Exception $expectedException,
        ?callable $initializer = null
    ) {
        $handler = WaitActionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($action);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'value is null' => [
                'action' => $actionParser->parse('wait'),
                'expectedException' => new UnsupportedValueException('')
            ],
            'value identifier cannot be extracted' => [
                'action' => $actionParser->parse('wait $".duration"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".duration"'
                ),
                'initializer' => function (WaitActionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".duration"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        WaitActionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }
}
