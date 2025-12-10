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

        $stepName = md5((string) rand());
        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $comparisonHandler = \Mockery::mock(ComparisonAssertionHandler::class);
        $comparisonHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $stepName,
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals(new Metadata($stepName, $assertion), $passedMetadata);

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

        $stepName = md5((string) rand());
        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $existenceHandler = \Mockery::mock(ExistenceAssertionHandler::class);
        $existenceHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $stepName,
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals(new Metadata($stepName, $assertion), $passedMetadata);

                return true;
            })
            ->andReturn($expectedReturnValue)
        ;

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            $existenceHandler,
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $stepName));
    }

    public function testHandleIsRegExp(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title is-regexp');

        $stepName = md5((string) rand());
        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $isRegExpHandler = \Mockery::mock(IsRegExpAssertionHandler::class);
        $isRegExpHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $stepName,
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals(new Metadata($stepName, $assertion), $passedMetadata);

                return true;
            })
            ->andReturn($expectedReturnValue)
        ;

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            \Mockery::mock(ExistenceAssertionHandler::class),
            $isRegExpHandler
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion, $stepName));
    }

    public function testHandleWrapsUnsupportedContentException(): void
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$elements.examined is "value"');

        $stepName = md5((string) rand());
        $expectedUnsupportedContentException = new UnsupportedContentException(
            UnsupportedContentException::TYPE_VALUE,
            '$elements.examined'
        );

        $comparisonHandler = \Mockery::mock(ComparisonAssertionHandler::class);
        $comparisonHandler
            ->shouldReceive('handle')
            ->withArgs(function (
                AssertionInterface $passedAssertion,
                Metadata $passedMetadata
            ) use (
                $assertion,
                $stepName,
            ) {
                self::assertSame($assertion, $passedAssertion);
                self::assertEquals(new Metadata($stepName, $assertion), $passedMetadata);

                return true;
            })
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
