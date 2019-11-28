<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedComparisonException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedIdentifierException;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceComparisonHandler;
use webignition\BasilDataStructure\Assertion;
use webignition\BasilDataStructure\AssertionInterface;
use webignition\BasilParser\AssertionParser;

/**
 * @group poc208
 */
class ExistenceComparisonHandlerTest extends AbstractTestCase
{
    /**
     * @var ExistenceComparisonHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ExistenceComparisonHandler::createHandler();
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(AssertionInterface $assertion, \Exception $expectedException)
    {
        $handler = ExistenceComparisonHandler::createHandler();

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'identifier is null' => [
                'assertion' => new Assertion('exists', null, 'exists'),
                'expectedException' => new UnsupportedIdentifierException(null),
            ],
            'comparison is null' => [
                'assertion' => new Assertion('exists', '$".selector"', null),
                'expectedException' => new UnsupportedComparisonException(null),
            ],
            'identifier is not supported' => [
                'assertion' => $assertionParser->parse('$elements.element_name exists'),
                'expectedException' => new UnsupportedIdentifierException('$elements.element_name'),
            ],
        ];
    }
}
