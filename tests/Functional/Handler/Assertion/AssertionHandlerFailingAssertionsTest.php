<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\AssertionParser;

class AssertionHandlerFailingAssertionsTest extends AbstractBrowserTestCase
{
    private AssertionHandler $handler;

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
        string $expectedExpectationFailedExceptionMessage
    ): void {
        $stepName = md5((string) rand());

        $source = $this->handler->handle($assertion, $stepName);
        $classCode = $this->testCodeGenerator->createBrowserTestForBlock($source, $fixture);

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

    /**
     * @return array<mixed>
     */
    public static function createSourceForFailingAssertionsDataProvider(): array
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
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$"h1".attribute_name exists'),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$env.FOO exists'),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
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
            ],
            'exists comparison, element identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$"2" exists'),
                'expectedExpectationFailedExceptionMessage' => 'Invalid locator',
            ],
            'exists comparison, attribute identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$"2".attribute_name exists'),
                'expectedExpectationFailedExceptionMessage' => 'Invalid locator',
            ],
        ];
    }
}
