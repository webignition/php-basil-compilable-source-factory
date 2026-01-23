<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Step;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\StepParser;

class StepHandlerTest extends AbstractBrowserTestCase
{
    private StepHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = StepHandler::createHandler();
    }

    #[DataProvider('handleDataProvider')]
    public function testHandle(
        string $fixture,
        StepInterface $step,
        ?BodyInterface $teardownStatements = null
    ): void {
        $source = $this->handler->handle($step);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            null,
            $teardownStatements
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

    /**
     * @return array<mixed>
     */
    public static function handleDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'single click action' => [
                'fixture' => '/action-click-submit.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $"#link-to-index"',
                    ],
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
            ],
            'single is assertion' => [
                'fixture' => '/assertions.html',
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is ".selector content"',
                    ],
                ]),
                'teardownStatements' => null,
            ],
            'single matches assertion' => [
                'fixture' => '/assertions.html',
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".matches-examined" matches $".matches-expected"',
                    ],
                ]),
                'teardownStatements' => null,
            ],
            'single click action, single assertion' => [
                'fixture' => '/action-click-submit.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $"#link-to-index"',
                    ],
                    'assertions' => [
                        '$page.title is "Test fixture web server default document"',
                    ],
                ]),
                'teardownStatements' => null,
            ],
            'multiple actions, multiple assertions' => [
                'fixture' => '/form.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $"input[name=radio-not-checked][value=not-checked-2]"',
                        'click $"input[name=radio-checked][value=checked-3]"',
                    ],
                    'assertions' => [
                        '$"input[name=radio-not-checked]" is "not-checked-2"',
                        '$"input[name=radio-checked]" is "checked-3"',
                    ],
                ]),
                'teardownStatements' => null,
            ],
            'assertion uses selector containing single quotes' => [
                'fixture' => '/form.html',
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$"input[value=\"\'within single quotes\'\"]" is $"[name=input-with-single-quoted-value]"',
                    ],
                ]),
            ],
        ];
    }

    #[DataProvider('handleForFailingStatementsDataProvider')]
    public function testHandleForFailingStatements(
        string $fixture,
        StepInterface $step,
        string $expectedExpectationFailedExceptionMessage,
        ?BodyInterface $additionalSetupStatements = null,
        ?BodyInterface $teardownStatements = null
    ): void {
        $source = $this->handler->handle($step);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            $additionalSetupStatements,
            $teardownStatements
        );

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

        $output = $testRunJob->getOutputAsString();

        $firstBracePosition = (int) strpos($output, '{');
        $json = substr($output, $firstBracePosition);

        $lastBracePosition = (int) strrpos($json, '}');
        $json = substr($json, 0, $lastBracePosition + 1);

        $this->assertJsonStringEqualsJsonString($expectedExpectationFailedExceptionMessage, $json);
    }

    /**
     * @return array<mixed>
     */
    public static function handleForFailingStatementsDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'wait action, element identifier examined value, element does not exist' => [
                'fixture' => '/action-wait.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'wait $".non-existent"',
                    ],
                ]),
                'expectedExpectationFailedExceptionMessage' => <<< 'EOD'
                    {
                        "examined": false,
                        "expected": true,
                        "stage": "execute",
                        "statement": {
                            "container": {
                                "operator": "exists",
                                "type": "derived-value-operation-assertion",
                                "value": "$\".non-existent\""
                            },
                            "statement": {
                                "arguments": "$\".non-existent\"",
                                "index": 0,
                                "source": "wait $\".non-existent\"",
                                "statement-type": "action",
                                "type": "wait",
                                "value": "$\".non-existent\""
                            }
                        }
                    }
                    EOD,
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'wait, attribute identifier examined value, element does not exist' => [
                'fixture' => '/action-wait.html',
                'step' => $stepParser->parse([
                    'actions' => [
                        'wait $".non-existent".attribute_name',
                    ],
                ]),
                'expectedExpectationFailedExceptionMessage' => <<< 'EOD'
                    {
                        "examined": false,
                        "expected": true,
                        "stage": "execute",
                        "statement": {
                            "container": {
                                "operator": "exists",
                                "type": "derived-value-operation-assertion",
                                "value": "$\".non-existent\""
                            },
                            "statement": {
                                "container": {
                                    "operator": "exists",
                                    "type": "derived-value-operation-assertion",
                                    "value": "$\".non-existent\".attribute_name"
                                },
                                "statement": {
                                    "arguments": "$\".non-existent\".attribute_name",
                                    "index": 0,
                                    "source": "wait $\".non-existent\".attribute_name",
                                    "statement-type": "action",
                                    "type": "wait",
                                    "value": "$\".non-existent\".attribute_name"
                                }
                            }
                        }
                    }
                    EOD,
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'exists comparison, element identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$"2" exists',
                    ],
                ]),
                'expectedExpectationFailedExceptionMessage' => <<< 'EOD'
                    {
                        "context": {
                            "locator": "2",
                            "type": "css"
                        },
                        "exception": {
                            "class": "webignition\\SymfonyDomCrawlerNavigator\\Exception\\InvalidLocatorException",
                            "code": 0,
                            "message": "Invalid CSS selector locator 2"
                        },
                        "stage": "setup",
                        "statement": {
                            "identifier": "$\"2\"",
                            "index": 0,
                            "operator": "exists",
                            "source": "$\"2\" exists",
                            "statement-type": "assertion"
                        }
                    }
                    EOD,
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
            ],
            'exists comparison, attribute identifier examined value, invalid locator exception is caught' => [
                'fixture' => '/index.html',
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$"2".attribute_name exists',
                    ],
                ]),
                'expectedExpectationFailedExceptionMessage' => <<<'EOD'
                    {
                        "context": {
                            "locator": "2",
                            "type": "css"
                        },
                        "exception": {
                            "class": "webignition\\SymfonyDomCrawlerNavigator\\Exception\\InvalidLocatorException",
                            "code": 0,
                            "message": "Invalid CSS selector locator 2"
                        },
                        "stage": "setup",
                        "statement": {
                            "identifier": "$\"2\".attribute_name",
                            "index": 0,
                            "operator": "exists",
                            "source": "$\"2\".attribute_name exists",
                            "statement-type": "assertion"
                        }
                    }
                    EOD,
            ],
        ];
    }
}
