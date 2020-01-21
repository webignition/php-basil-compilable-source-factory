<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Tests\Services\ObjectReflector;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Action\SetActionHandler;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilModels\Action\InputActionInterface;
use webignition\BasilParser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class SetActionHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        InputActionInterface $action,
        \Exception $expectedException,
        ?callable $initializer = null
    ) {
        $handler = SetActionHandler::createHandler();

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
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('set $elements.element_name to "value"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$elements.element_name'
                ),
            ],
            'identifier is attribute reference' => [
                'action' => $actionParser->parse('set $".selector".attribute_name to "value"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector".attribute_name'
                ),
            ],
            'identifier cannot be extracted' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector"'
                ),
                'initializer' => function (SetActionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".selector"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        SetActionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
            'value identifier cannot be extracted' => [
                'action' => $actionParser->parse('set $".selector" to $".value"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".value"'
                ),
                'initializer' => function (SetActionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);

                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".selector"')
                        ->andReturn(new ElementIdentifier('.selector'));

                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".value"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        SetActionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }
}
