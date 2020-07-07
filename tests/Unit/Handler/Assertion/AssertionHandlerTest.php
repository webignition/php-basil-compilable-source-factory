<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\IsRegExpAssertionHandler;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilParser\AssertionParser;

class AssertionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleComparison()
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title is "value"');

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $comparisonHandler = \Mockery::mock(ComparisonAssertionHandler::class);
        $comparisonHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue);

        $handler = new AssertionHandler(
            $comparisonHandler,
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion));
    }

    public function testHandleExistence()
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title exists');

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $existenceHandler = \Mockery::mock(ExistenceAssertionHandler::class);
        $existenceHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue);

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            $existenceHandler,
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion));
    }

    public function testHandleIsRegExp()
    {
        $assertionParser = AssertionParser::create();
        $assertion = $assertionParser->parse('$page.title is-regexp');

        $expectedReturnValue = \Mockery::mock(BodyInterface::class);

        $isRegExpHandler = \Mockery::mock(IsRegExpAssertionHandler::class);
        $isRegExpHandler
            ->shouldReceive('handle')
            ->with($assertion)
            ->andReturn($expectedReturnValue);

        $handler = new AssertionHandler(
            \Mockery::mock(ComparisonAssertionHandler::class),
            \Mockery::mock(ExistenceAssertionHandler::class),
            $isRegExpHandler
        );

        $this->assertSame($expectedReturnValue, $handler->handle($assertion));
    }

    public function testHandleWrapsUnsupportedContentException()
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
            ->andThrow($expectedUnsupportedContentException);

        $handler = new AssertionHandler(
            $comparisonHandler,
            \Mockery::mock(ExistenceAssertionHandler::class),
            \Mockery::mock(IsRegExpAssertionHandler::class)
        );

        $this->expectExceptionObject(new UnsupportedStatementException(
            $assertion,
            $expectedUnsupportedContentException
        ));

        $handler->handle($assertion);
    }

    public function testHandleThrowsUnsupportedStatementException()
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

        $handler->handle($assertion);
    }
}
