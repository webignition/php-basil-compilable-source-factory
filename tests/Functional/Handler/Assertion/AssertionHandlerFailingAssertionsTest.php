<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedValueOperationAssertion;
use webignition\BasilParser\AssertionParser;

class AssertionHandlerFailingAssertionsTest extends AbstractBrowserTestCase
{
    /**
     * @var AssertionHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = AssertionHandler::createHandler();
    }

    /**
     * @dataProvider createSourceForFailingAssertionsDataProvider
     */
    public function testHandleForFailingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        string $expectedExpectationFailedExceptionMessage,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($assertion);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            null,
            null,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode, 1);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );

            $this->assertStringContainsString(
                $expectedExpectationFailedExceptionMessage,
                $testRunJob->getOutputAsString()
            );
        }
    }

    public function createSourceForFailingAssertionsDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, element identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                ],
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$"h1".attribute_name exists'),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                ],
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$env.FOO exists'),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'is-regexp operation, scalar identifier, literal value is not a regular expression' => [
                'fixture' => '/index.html',
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches "pattern"'),
                    '"pattern"',
                    'is-regexp'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that true is false.',
            ],
            'is-regexp operation, scalar identifier, elemental value is not a regular expression' => [
                'fixture' => '/index.html',
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $"h1"'),
                    '$"h1"',
                    'is-regexp'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that true is false.',
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
