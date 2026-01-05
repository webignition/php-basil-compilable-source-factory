<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IdentifierExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ScalarExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ExistenceAssertionHandlerTest extends TestCase
{
    public function testHandleScalar(): void
    {
        $assertionParser = AssertionParser::create();

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $assertion = $assertionParser->parse('$page.title exists', 0);

        $scalarHandler = \Mockery::mock(ScalarExistenceAssertionHandler::class);
        $scalarHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue)
        ;

        $handler = new ExistenceAssertionHandler(
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            $scalarHandler,
            \Mockery::mock(IdentifierExistenceAssertionHandler::class)
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion));
    }

    public function testHandleIdentifier(): void
    {
        $assertionParser = AssertionParser::create();

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $assertion = $assertionParser->parse('$".selector" exists', 0);

        $identifierHandler = \Mockery::mock(IdentifierExistenceAssertionHandler::class);
        $identifierHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue)
        ;

        $handler = new ExistenceAssertionHandler(
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            \Mockery::mock(ScalarExistenceAssertionHandler::class),
            $identifierHandler
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion));
    }

    public function testHandleThrowsUnsupportedContentException(): void
    {
        $assertion = new Assertion(
            'invalid exists',
            0,
            'invalid',
            'exists',
        );

        $handler = ExistenceAssertionHandler::createHandler();

        $this->expectExceptionObject(new UnsupportedContentException(
            UnsupportedContentException::TYPE_IDENTIFIER,
            'invalid'
        ));

        $handler->handle($assertion);
    }
}
