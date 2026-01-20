<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\DomIdentifier\FactoryInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\WaitForActionHandler;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\ObjectReflector\ObjectReflector;

class WaitForActionHandlerTest extends TestCase
{
    #[DataProvider('handleThrowsExceptionDataProvider')]
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
    public static function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'identifier is not dom identifier' => [
                'action' => $actionParser->parse('wait-for $elements.element_name', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$elements.element_name'
                ),
            ],
            'identifier is attribute reference' => [
                'action' => $actionParser->parse('wait-for $".selector".attribute_name', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector".attribute_name'
                ),
            ],
            'identifier cannot be extracted' => [
                'action' => $actionParser->parse('wait-for $".selector"', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".selector"'
                ),
                'initializer' => function (WaitForActionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(FactoryInterface::class);
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
