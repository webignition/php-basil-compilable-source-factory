<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IsRegExpAssertionHandler;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;

class AssertionHandlerTest extends TestCase
{
    public function testHandleComparison(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title is "value"');

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $stepName = md5((string) rand());
        $metadata = new Metadata($stepName, $assertion);

        $comparisonHandler = \Mockery::mock(ComparisonAssertionHandler::class);
        $comparisonHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $metadata
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals($metadata, $passedMetadata);

                return true;
            })
            ->andReturn($expectedReturnValue)
        ;

        $handler = new AssertionHandler(
            $comparisonHandler,
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $stepName));
    }

    public function testHandleExistence(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title exists');

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $existenceHandler = \Mockery::mock(ExistenceAssertionHandler::class);
        $existenceHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue)
        ;

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            $existenceHandler,
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $stepName = md5((string) rand());
        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $stepName));
    }

    public function testHandleIsRegExp(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title is-regexp');

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $isRegExpHandler = \Mockery::mock(IsRegExpAssertionHandler::class);
        $isRegExpHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue)
        ;

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            \Mockery::mock(ExistenceAssertionHandler::class),
            $isRegExpHandler
        );

        $stepName = md5((string) rand());
        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $stepName));
    }

    public function testHandleWrapsUnsupportedContentException(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$elements.examined is "value"');

        $expectedUnsupportedContentException = new UnsupportedContentException(
            UnsupportedContentException::TYPE_VALUE,
            '$elements.examined'
        );

        $comparisonHandler = \Mockery::mock(ComparisonAssertionHandler::class);
        $comparisonHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andThrow($expectedUnsupportedContentException)
        ;

        $handler = new AssertionHandler(
            $comparisonHandler,
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->expectExceptionObject(new UnsupportedStatementException(
            $assertion,
            $expectedUnsupportedContentException
        ));

        $stepName = md5((string) rand());
        $handler->handle($assertion, $stepName);
    }

    public function testHandleThrowsUnsupportedStatementException(): void
    {
        $assertion = new Assertion(
            '$".selector" invalid-comparison "value"',
            '$".selector"',
            'invalid-comparison',
            '"value"'
        );

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->expectExceptionObject(new UnsupportedStatementException($assertion));

        $stepName = md5((string) rand());
        $handler->handle($assertion, $stepName);
    }
}
