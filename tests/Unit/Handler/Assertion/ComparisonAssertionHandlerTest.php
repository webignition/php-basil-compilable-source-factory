<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ComparisonAssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;

class ComparisonAssertionHandlerTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        AssertionInterface $assertion,
        \Exception $expectedException,
        ?callable $initializer = null
    ): void {
        $handler = ComparisonAssertionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'unsupported comparison' => [
                'assertion' => $assertionParser->parse('$".selector" foo "value"', 0),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"', 0)
                ),
            ],
            'comparison; examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_VALUE,
                    '$elements.examined'
                ),
            ],
            'comparison; expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected', 0),
                'expectedException' => new UnsupportedContentException(
                    UnsupportedContentException::TYPE_VALUE,
                    '$elements.expected'
                ),
            ],
        ];
    }
}
