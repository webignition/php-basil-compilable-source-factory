<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModelFactory\AssertionFactory;

class AssertionHandlerFailingAssertionsTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return AssertionHandler::createHandler();
    }

    /**
     * @dataProvider createSourceForFailingAssertionsDataProvider
     */
    public function testCreateSourceForFailingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        string $expectedExpectationFailedExceptionMessage,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($assertion);

        $classCode = $this->testCodeGenerator->createBrowserTestForLineList(
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
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"h1".attribute_name exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.FOO exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
