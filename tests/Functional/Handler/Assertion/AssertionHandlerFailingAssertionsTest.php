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
        $source = $this->handler->handle($assertion);
        $classCode = $this->testCodeGenerator->createBrowserTestForBlock($source, $fixture);

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
                'assertion' => $assertionParser->parse('$".selector" exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$\".selector\" exists",
                        "index": 0,
                        "identifier": "$\".selector\"",
                        "operator": "exists"
                    },
                    "expected": null,
                    "examined": false
    EOD,
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists', 0),
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
                    "expected": null,
                    "examined": false
    EOD,
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$"h1".attribute_name exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$\"h1\".attribute_name exists",
                        "index": 0,
                        "identifier": "$\"h1\".attribute_name",
                        "operator": "exists"
                    },
                    "expected": null,
                    "examined": false
    EOD,
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$env.FOO exists', 0),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    "statement": {
                        "statement-type": "assertion",
                        "source": "$env.FOO exists",
                        "index": 0,
                        "identifier": "$env.FOO",
                        "operator": "exists"
                    },
                    "expected": null,
                    "examined": false
    EOD,
            ],
            'is-regexp operation, scalar identifier, literal value is not a regular expression' => [
                'fixture' => '/index.html',
                'assertion' => new DerivedValueOperationAssertion(
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
                'assertion' => new DerivedValueOperationAssertion(
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
                'assertion' => $assertionParser->parse('$"2" exists', 0),
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
                        "class": "webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException",
                        "code": 0,
                        "message": "Invalid CSS selector locator 2"
                    }
    EOD,
            ],
            'exists comparison, attribute identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'assertion' => $assertionParser->parse('$"2".attribute_name exists', 0),
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
                        "class": "webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException",
                        "code": 0,
                        "message": "Invalid CSS selector locator 2"
                    }
    EOD,
            ],
        ];
    }
}
