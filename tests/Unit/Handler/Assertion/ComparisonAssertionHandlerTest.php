<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ObjectReflector;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilModels\Assertion\ComparisonAssertionInterface;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class ComparisonAssertionHandlerTest extends AbstractTestCase
{
    /**
     * @var ComparisonAssertionHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ComparisonAssertionHandler::createHandler();
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        ComparisonAssertionInterface $assertion,
        \Exception $expectedException,
        ?callable $initializer = null
    ) {
        $handler = ComparisonAssertionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"'),
                'expectedException' => new UnsupportedValueException('$elements.examined'),
            ],
            'expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected'),
                'expectedException' => new UnsupportedValueException('$elements.expected'),
            ],
            'examined value identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".examined" is "value"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".examined"'
                ),
                'initializer' => function (ComparisonAssertionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".examined"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        ComparisonAssertionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
            'expected value identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".examined" is $".expected"'),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_IDENTIFIER,
                    '$".expected"'
                ),
                'initializer' => function (ComparisonAssertionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);

                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".examined"')
                        ->andReturn(new ElementIdentifier('.examined'));

                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".expected"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        ComparisonAssertionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }
}
