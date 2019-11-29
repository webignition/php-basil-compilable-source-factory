<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilDataStructure\AssertionInterface;
use webignition\BasilParser\AssertionParser;

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
    public function testHandleThrowsException(AssertionInterface $assertion, \Exception $expectedException)
    {
        $handler = ComparisonAssertionHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'expected value is null' => [
                'assertion' => $assertionParser->parse('$".selector" is'),
                'expectedException' => new UnsupportedValueException(null),
            ],
            'examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"'),
                'expectedException' => new UnsupportedValueException('$elements.examined'),
            ],
            'expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected'),
                'expectedException' => new UnsupportedValueException('$elements.expected'),
            ],
        ];
    }
}
