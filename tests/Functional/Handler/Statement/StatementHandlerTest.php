<?php

declare(strict_types=1);

namespace Functional\Handler\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\StatementInterface;
use webignition\BasilModels\Parser\AssertionParser;

class StatementHandlerTest extends AbstractBrowserTestCase
{
    use Action\BackActionFunctionalDataProviderTrait;
    use Action\ClickActionFunctionalDataProviderTrait;
    use Action\ForwardActionFunctionalDataProviderTrait;
    use Action\ReloadActionFunctionalDataProviderTrait;
    use Action\SetActionFunctionalDataProviderTrait;
    use Action\SubmitActionFunctionalDataProviderTrait;
    use Action\WaitActionFunctionalDataProviderTrait;
    use Action\WaitForActionFunctionalDataProviderTrait;
    use Assertion\EqualityAssertionFunctionalDataProviderTrait;
    use Assertion\InclusionAssertionFunctionalDataProviderTrait;
    use Assertion\ExcludesAssertionFunctionalDataProviderTrait;
    use Assertion\ExistsAssertionFunctionalDataProviderTrait;
    use Assertion\IncludesAssertionFunctionalDataProviderTrait;
    use Assertion\IsAssertionFunctionalDataProviderTrait;
    use Assertion\IsNotAssertionFunctionalDataProviderTrait;
    use Assertion\IsRegExpAssertionFunctionalDataProviderTrait;
    use Assertion\MatchesAssertionFunctionalDataProviderTrait;
    use Assertion\NotExistsAssertionFunctionalDataProviderTrait;

    private StatementHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = StatementHandler::createHandler();
    }

    /**
     * @param array<string, string> $additionalVariableIdentifiers
     */
    #[DataProvider('backActionFunctionalDataProvider')]
    #[DataProvider('clickActionFunctionalDataProvider')]
    #[DataProvider('forwardActionFunctionalDataProvider')]
    #[DataProvider('reloadActionFunctionalDataProvider')]
    #[DataProvider('setActionFunctionalDataProvider')]
    #[DataProvider('submitActionFunctionalDataProvider')]
    #[DataProvider('waitActionFunctionalDataProvider')]
    #[DataProvider('waitForActionFunctionalDataProvider')]
    #[DataProvider('excludesAssertionFunctionalDataProvider')]
    #[DataProvider('existsAssertionFunctionalDataProvider')]
    #[DataProvider('includesAssertionFunctionalDataProvider')]
    #[DataProvider('isAssertionFunctionalDataProvider')]
    #[DataProvider('isNotAssertionFunctionalDataProvider')]
    #[DataProvider('matchesAssertionFunctionalDataProvider')]
    #[DataProvider('notExistsAssertionFunctionalDataProvider')]
    #[DataProvider('isRegExpAssertionFunctionalDataProvider')]
    public function testHandleForPassingStatements(
        string $fixture,
        StatementInterface $statement,
        array $additionalVariableIdentifiers = [],
        ?BodyInterface $additionalSetupStatements = null,
        ?BodyInterface $teardownStatements = null,
    ): void {
        $components = $this->handler->handle($statement);

        $bodyParts = [];
        $setupComponent = $components->getSetup();
        if ($setupComponent instanceof BodyInterface) {
            $bodyParts[] = $setupComponent;
        }
        $bodyParts[] = $components->getBody();
        $body = new Body($bodyParts);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $body,
            $fixture,
            $additionalSetupStatements,
            $teardownStatements,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );
        }
    }

    #[DataProvider('createSourceForFailingAssertionsDataProvider')]
    public function testHandleForFailingStatements(
        string $fixture,
        StatementInterface $statement,
        string $expectedExpectationFailedExceptionMessage
    ): void {
        $components = $this->handler->handle($statement);

        $bodyParts = [];
        $setupComponent = $components->getSetup();
        if ($setupComponent instanceof BodyInterface) {
            $bodyParts[] = $setupComponent;
        }
        $bodyParts[] = $components->getBody();
        $body = new Body($bodyParts);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock($body, $fixture);

        $testRunJob = $this->testRunner->createTestRunJob($classCode, 1);

        if (!$testRunJob instanceof TestRunJob) {
            return;
        }

        $this->testRunner->run($testRunJob);

        $this->assertSame(
            $testRunJob->getExpectedExitCode(),
            $testRunJob->getExitCode(),
            $testRunJob->getOutputAsString()
        );

        $this->assertStringContainsString($expectedExpectationFailedExceptionMessage, $testRunJob->getOutputAsString());
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
                'statement' => $assertionParser->parse('$".selector" exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$\".selector\" exists",
                        "index": 0,
                        "identifier": "$\".selector\"",
                        "operator": "exists"
                    },
                    "expected": true,
                    "examined": false
    EOD,
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'statement' => $assertionParser->parse('$".selector".attribute_name exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "container": {
                            "value": "$\".selector\"",
                            "operator": "exists",
                            "type": "derived-value-operation-assertion"
                        },
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\".selector\".attribute_name exists",
                            "index": 0,
                            "identifier": "$\".selector\".attribute_name",
                            "operator": "exists"
                        }
                    },
                    "expected": true,
                    "examined": false
    EOD,
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'statement' => $assertionParser->parse('$"h1".attribute_name exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$\"h1\".attribute_name exists",
                        "index": 0,
                        "identifier": "$\"h1\".attribute_name",
                        "operator": "exists"
                    },
                    "expected": true,
                    "examined": false
    EOD,
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'statement' => $assertionParser->parse('$env.FOO exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$env.FOO exists",
                        "index": 0,
                        "identifier": "$env.FOO",
                        "operator": "exists"
                    },
                    "expected": true,
                    "examined": false
    EOD,
            ],
            'is-regexp operation, scalar identifier, literal value is not a regular expression' => [
                'fixture' => '/index.html',
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches "pattern"', 0),
                    '"pattern"',
                    'is-regexp'
                ),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "container": {
                            "value": "\"pattern\"",
                            "operator": "is-regexp",
                            "type": "derived-value-operation-assertion"
                        },
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$page.title matches \"pattern\"",
                            "index": 0,
                            "identifier": "$page.title",
                            "value": "\"pattern\"",
                            "operator": "matches"
                        }
                    },
                    "expected": true,
                    "examined": "pattern"
    EOD,
            ],
            'is-regexp operation, scalar identifier, elemental value is not a regular expression' => [
                'fixture' => '/index.html',
                'statement' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $"h1"', 0),
                    '$"h1"',
                    'is-regexp'
                ),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "container": {
                            "value": "$\"h1\"",
                            "operator": "is-regexp",
                            "type": "derived-value-operation-assertion"
                        },
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$page.title matches $\"h1\"",
                            "index": 0,
                            "identifier": "$page.title",
                            "value": "$\"h1\"",
                            "operator": "matches"
                        }
                    },
                    "expected": true,
                    "examined": "Test fixture web server default document"
    EOD,
            ],
            'exists comparison, element identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'statement' => $assertionParser->parse('$"2" exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$\"2\" exists",
                        "index": 0,
                        "identifier": "$\"2\"",
                        "operator": "exists"
                    },
                    "reason": "locator-invalid",
                    "exception": {
                        "class": "webignition\\SymfonyDomCrawlerNavigator\\Exception\\InvalidLocatorException",
                        "code": 0,
                        "message": "Invalid CSS selector locator 2"
                    }
    EOD,
            ],
            'exists comparison, attribute identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'statement' => $assertionParser->parse('$"2".attribute_name exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "container": {
                            "value": "$\"2\"",
                            "operator": "exists",
                            "type": "derived-value-operation-assertion"
                        },
                        "statement": {
                            "statement-type": "assertion",
                            "source": "$\"2\".attribute_name exists",
                            "index": 0,
                            "identifier": "$\"2\".attribute_name",
                            "operator": "exists"
                        }
                    },
                    "reason": "locator-invalid",
                    "exception": {
                        "class": "webignition\\SymfonyDomCrawlerNavigator\\Exception\\InvalidLocatorException",
                        "code": 0,
                        "message": "Invalid CSS selector locator 2"
                    }
    EOD,
            ],
        ];
    }
}
