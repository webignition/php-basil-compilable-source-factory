<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BaseBasilTestCase\Enum\StatementStage;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandler;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandlerCollections;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\TryCatchBlockFactory;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\BasilModels\Parser\StepParser;

class StepHandlerTest extends AbstractResolvableTestCase
{
    #[DataProvider('handleSuccessDataProvider')]
    public function testHandleSuccess(
        StepInterface $step,
        StepHandler $handler,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $contentCollection = $handler->handle($step);

        $this->assertRenderResolvable($expectedRenderedContent, new Body($contentCollection));
        self::assertEquals($expectedMetadata, $contentCollection->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function handleSuccessDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'empty step' => [
                'step' => $stepParser->parse([]),
                'handler' => self::createStepHandler(),
                'expectedRenderedContent' => '',
                'expectedMetadata' => new Metadata(),
            ],
            'single click action, no setup, body might throw' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector"' => new StatementHandlerCollections(
                            BodyContentCollection::createFromExpressions([
                                new MethodInvocation(
                                    methodName: 'method',
                                    arguments: new MethodArguments([
                                        LiteralExpression::string(
                                            'StatementHandler::handle(click $".selector")::body',
                                        ),
                                    ]),
                                    mightThrow: true,
                                    type: TypeCollection::string(),
                                )
                            ]),
                        ),
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)::body'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)::body

                    // StatementBlockFactory::create(click $".selector")
                    try {
                        method(StatementHandler::handle(click $".selector")::body);
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "action",
                                    "source": "click $\\".selector\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "type": "click",
                                    "arguments": "$\\".selector\\""
                                }',
                                $exception,
                                StatementStage::EXECUTE,
                            ),
                        );
                    }

                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'single click action, no setup, body will not throw' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector")::body')
                                ),
                        ),
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)::body')
                                ),
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)::body

                    // StatementBlockFactory::create(click $".selector")
                    // StatementHandler::handle(click $".selector")::body

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single click action, has setup, setup will not throw' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector")::body'),
                                )
                        )->withSetup(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector")::setup'),
                                )
                        ),
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)

                    // StatementBlockFactory::create(click $".selector")
                    // StatementHandler::handle(click $".selector")::setup

                    // StatementHandler::handle(click $".selector")::body

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single click action, has setup, setup might throw' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector")::body'),
                                )
                        )->withSetup(
                            new BodyContentCollection()
                                ->append(
                                    new Statement(
                                        new MethodInvocation(
                                            methodName: 'method',
                                            arguments: new MethodArguments([
                                                LiteralExpression::string(
                                                    '"StatementHandler::handle(click $\".selector\")::setup"',
                                                ),
                                            ]),
                                            mightThrow: true,
                                            type: TypeCollection::string(),
                                        )
                                    ),
                                )
                        ),
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)

                    // StatementBlockFactory::create(click $".selector")
                    try {
                        method("StatementHandler::handle(click $\".selector\")::setup");
                    } catch (\Throwable $exception) {
                        {{ PHPUNIT }}->fail(
                            {{ MESSAGE_FACTORY }}->createFailureMessage(
                                '{
                                    "statement-type": "action",
                                    "source": "click $\\".selector\\"",
                                    "index": 0,
                                    "identifier": "$\\".selector\\"",
                                    "type": "click",
                                    "arguments": "$\\".selector\\""
                                }',
                                $exception,
                                StatementStage::SETUP,
                            ),
                        );
                    }

                    // StatementHandler::handle(click $".selector")::body

                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        StatementStage::class,
                        \Throwable::class,
                    ],
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
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
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector1"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector1")::body'),
                                )
                        ),
                        'click $".selector2"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector2")::body'),
                                )
                        ),
                        '$".selector1" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector1" exists)::body'),
                                )
                        ),
                        '$".selector2" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector2" exists)::body'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector1" exists)
                    // StatementHandler::handle($".selector1" exists)::body

                    // StatementBlockFactory::create($".selector2" exists)
                    // StatementHandler::handle($".selector2" exists)::body

                    // StatementBlockFactory::create(click $".selector1")
                    // StatementHandler::handle(click $".selector1")::body

                    // StatementBlockFactory::create(click $".selector2")
                    // StatementHandler::handle(click $".selector2")::body

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single element exists assertion, no setup' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)::body'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)::body

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single attribute exists assertion, no setup' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector".attribute_name exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle($".selector" exists)::body'
                                    ),
                                )
                        ),
                        '$".selector".attribute_name exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle($".selector".attribute_name exists)::body'
                                    ),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)::body
                    
                    // StatementBlockFactory::create($".selector".attribute_name exists)
                    // StatementHandler::handle($".selector".attribute_name exists)::body

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'single exists assertion, has setup, will not throw' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".selector" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)::body'),
                                )
                        )->withSetup(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector" exists)::setup'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector" exists)
                    // StatementHandler::handle($".selector" exists)::setup
                    
                    // StatementHandler::handle($".selector" exists)::body

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
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".parent" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".parent" exists)::body'),
                                )
                        ),
                        '$".parent" >> $".child" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle($".parent" >> $".child" exists)::body'
                                    ),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".parent" exists)
                    // StatementHandler::handle($".parent" exists)::body

                    // StatementBlockFactory::create($".parent" >> $".child" exists)
                    // StatementHandler::handle($".parent" >> $".child" exists)::body

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
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".selector1" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector1" exists)::body'),
                                )
                        ),
                        '$".selector2" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector2" exists)::body'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector1" exists)
                    // StatementHandler::handle($".selector1" exists)::body

                    // StatementBlockFactory::create($".selector2" exists)
                    // StatementHandler::handle($".selector2" exists)::body

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
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        'click $".selector1"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle(click $".selector1")::body'),
                                )
                        ),
                        '$".selector1" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector1" exists)::body'),
                                )
                        ),
                        '$".selector2" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".selector2" exists)::body'),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".selector1" exists)
                    // StatementHandler::handle($".selector1" exists)::body

                    // StatementBlockFactory::create(click $".selector1")
                    // StatementHandler::handle(click $".selector1")::body

                    // StatementBlockFactory::create($".selector2" exists)
                    // StatementHandler::handle($".selector2" exists)::body

                    EOD,
                'expectedMetadata' => new Metadata(),
            ],
            'two descendant exists assertions with common parent' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".parent" >> $".child1" exists',
                        '$".parent" >> $".child2" exists',
                    ],
                ]),
                'handler' => self::createStepHandler([
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '$".parent" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('StatementHandler::handle($".parent" exists)::body'),
                                )
                        ),
                        '$".parent" >> $".child1" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle($".parent" >> $".child1" exists)::body'
                                    ),
                                )
                        ),
                        '$".parent" >> $".child2" exists' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle($".parent" >> $".child2" exists)::body'
                                    ),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create($".parent" exists)
                    // StatementHandler::handle($".parent" exists)::body

                    // StatementBlockFactory::create($".parent" >> $".child1" exists)
                    // StatementHandler::handle($".parent" >> $".child1" exists)::body

                    // StatementBlockFactory::create($".parent" >> $".child2" exists)
                    // StatementHandler::handle($".parent" >> $".child2" exists)::body

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
                    StatementBlockFactory::class => self::createMockStatementBlockFactory(),
                    StatementHandler::class => self::createMockStatementHandler([
                        '"/pattern/" is-regexp' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle("/pattern/" is-regexp)::body'
                                    ),
                                )
                        ),
                        '$page.title matches "/pattern/"' => new StatementHandlerCollections(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment(
                                        'StatementHandler::handle($page.title matches "/pattern/")::body'
                                    ),
                                )
                        ),
                    ]),
                ]),
                'expectedRenderedContent' => <<< 'EOD'
                    // StatementBlockFactory::create("/pattern/" is-regexp)
                    // StatementHandler::handle("/pattern/" is-regexp)::body

                    // StatementBlockFactory::create($page.title matches "/pattern/")
                    // StatementHandler::handle($page.title matches "/pattern/")::body

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

    private static function createMockStatementBlockFactory(): StatementBlockFactory
    {
        $statementBlockFactory = \Mockery::mock(StatementBlockFactory::class);

        $statementBlockFactory
            ->shouldReceive('create')
            ->andReturnUsing(function (StatementInterface $statement) {
                return new BodyContentCollection()
                    ->append(new SingleLineComment(
                        'StatementBlockFactory::create(' . $statement->getSource() . ')'
                    ))
                ;
            })
        ;

        return $statementBlockFactory;
    }

    /**
     * @param array<string, ?StatementHandlerCollections> $handleCalls
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
                    if (null === $data) {
                        throw new \RuntimeException(
                            sprintf(
                                'Mock StatementHandler::create() call missing for "%s"',
                                $statement->getSource()
                            )
                        );
                    }

                    return $data;
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
