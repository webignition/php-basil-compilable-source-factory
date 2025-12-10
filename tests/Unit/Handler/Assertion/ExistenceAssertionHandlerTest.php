<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IdentifierExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ScalarExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ExistenceAssertionHandlerTest extends TestCase
{
    public function testHandleScalar(): void
    {
        $assertionParser = AssertionParser::create();

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $stepName = md5((string) rand());
        $assertion = $assertionParser->parse('$page.title exists');
        $metadata = new Metadata($stepName, $assertion);

        $scalarHandler = \Mockery::mock(ScalarExistenceAssertionHandler::class);
        $scalarHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $stepName
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals(new Metadata($stepName, $assertion), $passedMetadata);

                return true;
            })
            ->andReturn($expectedReturnValue)
        ;

        $handler = new ExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            $scalarHandler,
            \Mockery::mock(IdentifierExistenceAssertionHandler::class)
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $metadata));
    }

    public function testHandleIdentifier(): void
    {
        $assertionParser = AssertionParser::create();

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $stepName = md5((string) rand());
        $assertion = $assertionParser->parse('$".selector" exists');
        $metadata = new Metadata($stepName, $assertion);

        $identifierHandler = \Mockery::mock(IdentifierExistenceAssertionHandler::class);
        $identifierHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $stepName
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals(new Metadata($stepName, $assertion), $passedMetadata);

                return true;
            })
            ->andReturn($expectedReturnValue)
        ;

        $handler = new ExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            \Mockery::mock(ScalarExistenceAssertionHandler::class),
            $identifierHandler
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $metadata));
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

        $stepName = md5((string) rand());
        $metadata = new Metadata($stepName, $assertion);

        $handler->handle($assertion, $metadata);
    }
}
