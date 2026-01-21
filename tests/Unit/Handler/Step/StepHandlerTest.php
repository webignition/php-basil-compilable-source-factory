<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandler;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandlerComponents;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\StatementInterface;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Parser\StepParser;
use webignition\Stubble\Resolvable\ResolvableInterface;

class StepHandlerTest extends AbstractResolvableTestCase
{
    #[DataProvider('handleSuccessDataProvider')]
    public function testHandleSuccess(
        StepInterface $step,
        StepHandler $handler,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $handler->handle($step);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        self::assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function handleSuccessDataProvider(): array
    {
        $stepParser = StepParser::create();
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'empty step' => [
                'step' => $stepParser->parse([]),
                'handler' => self::createStepHandler(),
                'expectedRenderedContent' => '',
                'expectedMetadata' => new Metadata(),
            ],
            'single click action' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector"', 0),
                                '$".selector"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector" exists)'
                                ),
                            ]),
                        ],
                        'click $".selector"' => [
                            'statement' => $actionParser->parse('click $".selector"', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector")'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector"' => [
                            'statement' => $actionParser->parse('click $".selector"', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle(click $".selector")'),
                                ])
                            ),
                        ],
                        '$".selector" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector"', 0),
                                '$".selector"',
                                'exists'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)'),
                                ])
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)

                    // StatementBlockFactory::create(click $".selector")
                    try {
                        // StatementHandler::handle(click $".selector")
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "action",
                                    "source": "click $\\".selector\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "type": "click",
                                    "arguments": "$\\".selector\\""
                                }',
                                StatementStage::EXECUTE,
                                $exception,
                            ),
                        );
                    }

                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
            ],
            'two click actions' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector1"',
                        'click $".selector2"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".selector1" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"', 0),
                                '$".selector1"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector2"', 1),
                                '$".selector2"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
                                ),
                            ]),
                        ],
                        'click $".selector2"' => [
                            'statement' => $actionParser->parse('click $".selector2"', 1),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector2")'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle(click $".selector1")'),
                                ]),
                            )
                        ],
                        'click $".selector2"' => [
                            'statement' => $actionParser->parse('click $".selector2"', 1),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle(click $".selector2")'),
                                ]),
                            ),
                        ],
                        '$".selector1" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"', 0),
                                '$".selector1"',
                                'exists'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector1" exists)'),
                                ]),
                            ),
                        ],
                        '$".selector2" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector2"', 1),
                                '$".selector2"',
                                'exists'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector2" exists)'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector1" exists)
                    // StatementHandler::handle($".selector1" exists)

                    // StatementBlockFactory::create(click $".selector1")
                    try {
                        // StatementHandler::handle(click $".selector1")
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "action",
                                    "source": "click $\\".selector1\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector1\\"",
                                    "type": "click",
                                    "arguments": "$\\".selector1\\""
                                }',
                                StatementStage::EXECUTE,
                                $exception,
                            ),
                        );
                    }

                    // StatementBlockFactory::create($".selector2" exists)
                    // StatementHandler::handle($".selector2" exists)

                    // StatementBlockFactory::create(click $".selector2")
                    try {
                        // StatementHandler::handle(click $".selector2")
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "action",
                                    "source": "click $\\".selector2\\"",
                                    "index": 1,
                                    "identifier": "$\\".selector2\\"",
                                    "type": "click",
                                    "arguments": "$\\".selector2\\""
                                }',
                                StatementStage::EXECUTE,
                                $exception,
                            ),
                        );
                    }

                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
            ],
            'single exists assertion' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'statement' => $assertionParser->parse('$".selector" exists', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".selector" exists' => [
                            'statement' => $assertionParser->parse('$".selector" exists', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single exists assertion, descendant identifier' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".parent" >> $".child" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child" exists', 0),
                                '$".parent"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" exists)'
                                ),
                            ]),
                        ],
                        '$".parent" >> $".child" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child" exists', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" >> $".child" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".parent" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child" exists', 0),
                                '$".parent"',
                                'exists'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".parent" exists)'),
                                ]),
                            ),
                        ],
                        '$".parent" >> $".child" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child" exists', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".parent" >> $".child" exists)'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".parent" exists)
                    // StatementHandler::handle($".parent" exists)
                    
                    // StatementBlockFactory::create($".parent" >> $".child" exists)
                    // StatementHandler::handle($".parent" >> $".child" exists)

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'two exists assertions' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector1" exists',
                        '$".selector2" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".selector1" exists' => [
                            'statement' => $assertionParser->parse('$".selector1" exists', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists', 1),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".selector1" exists' => [
                            'statement' => $assertionParser->parse('$".selector1" exists', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector1" exists)'),
                                ]),
                            ),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists', 1),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector2" exists)'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector1" exists)
                    // StatementHandler::handle($".selector1" exists)
                    
                    // StatementBlockFactory::create($".selector2" exists)
                    // StatementHandler::handle($".selector2" exists)

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single click action, single exists assertion' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector1"',
                    ],
                    'assertions' => [
                        '$".selector2" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".selector1" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"', 0),
                                '$".selector1"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists', 1),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle(click $".selector1")'),
                                ]),
                            ),
                        ],
                        '$".selector1" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"', 0),
                                '$".selector1"',
                                'exists'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector1" exists)'),
                                ]),
                            ),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists', 1),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".selector2" exists)'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector1" exists)
                    // StatementHandler::handle($".selector1" exists)

                    // StatementBlockFactory::create(click $".selector1")
                    try {
                        // StatementHandler::handle(click $".selector1")
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ FAILURE_MESSAGE_FACTORY }}->create(
                                '{
                                    "statement-type": "action",
                                    "source": "click $\\".selector1\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector1\\"",
                                    "type": "click",
                                    "arguments": "$\\".selector1\\""
                                }',
                                StatementStage::EXECUTE,
                                $exception,
                            ),
                        );
                    }

                    // StatementBlockFactory::create($".selector2" exists)
                    // StatementHandler::handle($".selector2" exists)

                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::FAILURE_MESSAGE_FACTORY,
                    ],
                ),
            ],
            'two descendant exists assertions with common parent' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".parent" >> $".child1" exists',
                        '$".parent" >> $".child2" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child1" exists', 0),
                                '$".parent"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" exists)'
                                ),
                            ]),
                        ],
                        '$".parent" >> $".child1" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child1" exists', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" >> $".child1" exists)'
                                ),
                            ]),
                        ],
                        '$".parent" >> $".child2" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child2" exists', 1),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" >> $".child2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".parent" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child1" exists', 0),
                                '$".parent"',
                                'exists'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".parent" exists)'),
                                ]),
                            ),
                        ],
                        '$".parent" >> $".child1" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child1" exists', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".parent" >> $".child1" exists)'),
                                ]),
                            ),
                        ],
                        '$".parent" >> $".child2" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child2" exists', 1),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($".parent" >> $".child2" exists)'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".parent" exists)
                    // StatementHandler::handle($".parent" exists)
                    
                    // StatementBlockFactory::create($".parent" >> $".child1" exists)
                    // StatementHandler::handle($".parent" >> $".child1" exists)
                    
                    // StatementBlockFactory::create($".parent" >> $".child2" exists)
                    // StatementHandler::handle($".parent" >> $".child2" exists)

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'derived is-regexp' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$page.title matches "/pattern/"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory([
                        '"/pattern/" is-regexp' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$page.title matches "/pattern/"', 0),
                                '"/pattern/"',
                                'is-regexp'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create("/pattern/" is-regexp)'
                                ),
                            ]),
                        ],
                        '$page.title matches "/pattern/"' => [
                            'statement' => $assertionParser->parse('$page.title matches "/pattern/"', 0),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($page.title matches "/pattern/")'
                                ),
                            ]),
                        ],
                    ]),
                    StatementHandler::class => self::createMockStatementHandler([
                        '"/pattern/" is-regexp' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$page.title matches "/pattern/"', 0),
                                '"/pattern/"',
                                'is-regexp'
                            ),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment(
                                        'StatementHandler::handle("/pattern/" is-regexp)'
                                    ),
                                ]),
                            ),
                        ],
                        '$page.title matches "/pattern/"' => [
                            'statement' => $assertionParser->parse('$page.title matches "/pattern/"', 0),
                            'return' => new StatementHandlerComponents(
                                new Body([
                                    new SingleLineComment('StatementHandler::handle($page.title matches "/pattern/")'),
                                ]),
                            ),
                        ],
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create("/pattern/" is-regexp)
                    // StatementHandler::handle("/pattern/" is-regexp)
                    
                    // StatementBlockFactory::create($page.title matches "/pattern/")
                    // StatementHandler::handle($page.title matches "/pattern/")

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    #[DataProvider('handleThrowsExceptionDataProvider')]
    public function testHandleThrowsException(StepInterface $step, UnsupportedStepException $expectedException): void
    {
        $handler = StepHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($step);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $stepParser = StepParser::create();

        return [
            'interaction action, identifier not dom identifier' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $elements.element_name',
                    ],
                ]),
                'expectedException' => new UnsupportedStepException(
                    $stepParser->parse([
                        'actions' => [
                            'click $elements.element_name',
                        ],
                    ]),
                    new UnsupportedStatementException(
                        $actionParser->parse('click $elements.element_name', 0),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$elements.element_name'
                        )
                    )
                ),
            ],
        ];
    }

    public function testHandleAssertionWithUnsupportedIdentifier(): void
    {
        $stepParser = StepParser::create();
        $assertionParser = AssertionParser::create();

        $step = $stepParser->parse([
            'assertions' => [
                '$elements.examined is "value"',
            ],
        ]);

        $assertion = $assertionParser->parse('$elements.examined is "value"', 0);

        $unsupportedContentException = new UnsupportedContentException(
            UnsupportedContentException::TYPE_IDENTIFIER,
            '$elements.examined'
        );

        $derivedAssertionFactory = \Mockery::mock(DerivedAssertionFactory::class);
        $derivedAssertionFactory
            ->shouldReceive('createForAssertion')
            ->withArgs(function (AssertionInterface $passedAssertion) use ($assertion) {
                self::assertEquals($assertion, $passedAssertion);

                return true;
            })
            ->andThrow($unsupportedContentException)
        ;

        $handler = self::createStepHandler([
            DerivedAssertionFactory::class => $derivedAssertionFactory,
        ]);

        $this->expectExceptionObject(new UnsupportedStepException(
            $step,
            new UnsupportedStatementException($assertion, $unsupportedContentException)
        ));

        $handler->handle($step);
    }

    /**
     * @param array<string, array{"statement": StatementInterface, "return": ResolvableInterface}> $createCalls
     */
    private static function createMockStatementBlockFactory(array $createCalls): StatementBlockFactory
    {
        $statementBlockFactory = \Mockery::mock(StatementBlockFactory::class);

        if (0 !== count($createCalls)) {
            $statementBlockFactory
                ->shouldReceive('create')
                ->times(count($createCalls))
                ->andReturnUsing(function (StatementInterface $statement) use ($createCalls) {
                    $data = $createCalls[$statement->getSource()];

                    self::assertEquals($data['statement'], $statement);

                    return $data['return'];
                })
            ;
        }

        return $statementBlockFactory;
    }

    /**
     * @param array<string, array{"statement": StatementInterface, "return": StatementHandlerComponents}> $handleCalls
     */
    private static function createMockStatementHandler(array $handleCalls): StatementHandler
    {
        $handler = \Mockery::mock(StatementHandler::class);

        if (0 !== count($handleCalls)) {
            $handler
                ->shouldReceive('handle')
                ->times(count($handleCalls))
                ->andReturnUsing(function (StatementInterface $statement) use ($handleCalls) {
                    $data = $handleCalls[$statement->getSource()];

                    self::assertEquals($data['statement'], $statement);

                    return $data['return'];
                })
            ;
        }

        return $handler;
    }

    /**
     * @param array<mixed> $services
     */
    private static function createStepHandler(array $services = []): StepHandler
    {
        $statementHandler = $services[StatementHandler::class] ?? null;
        $statementHandler = $statementHandler instanceof StatementHandler
            ? $statementHandler
            : StatementHandler::createHandler();

        $statementBlockFactory = $services[StatementBlockFactory::class] ?? null;
        $statementBlockFactory = $statementBlockFactory instanceof StatementBlockFactory
            ? $statementBlockFactory
            : StatementBlockFactory::createFactory();

        $derivedAssertionFactory = $services[DerivedAssertionFactory::class] ?? null;
        $derivedAssertionFactory = $derivedAssertionFactory instanceof DerivedAssertionFactory
            ? $derivedAssertionFactory
            : DerivedAssertionFactory::createFactory();

        return new StepHandler(
            $statementHandler,
            $statementBlockFactory,
            $derivedAssertionFactory,
            TryCatchBlockFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
        );
    }
}
