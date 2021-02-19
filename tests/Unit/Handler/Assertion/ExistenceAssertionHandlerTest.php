<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IdentifierExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ScalarExistenceAssertionHandler;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilParser\AssertionParser;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ExistenceAssertionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleScalar(): void
    {
        $assertionParser = AssertionParser::create();

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $assertion = $assertionParser->parse('$page.title exists');
        $scalarHandler = \Mockery::mock(ScalarExistenceAssertionHandler::class);
        $scalarHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue);

        $handler = new ExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
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

        $assertion = $assertionParser->parse('$".selector" exists');
        $identifierHandler = \Mockery::mock(IdentifierExistenceAssertionHandler::class);
        $identifierHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue);

        $handler = new ExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
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
