<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\StatementInterface;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;

class StepHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider handleSuccessDataProvider
     */
    public function testHandleSuccess(
        StepInterface $step,
        StepHandler $handler,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $source = $handler->handle($step);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleSuccessDataProvider(): array
    {
        $stepParser = StepParser::create();
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'empty step' => [
                'step' => $stepParser->parse([]),
                'handler' => $this->createStepHandler(),
                'expectedRenderedSource' => '',
                'expectedMetadata' => new Metadata(),
            ],
            'single click action' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    DerivedAssertionFactory::class => $this->createMockDerivedAssertionFactory([
                        'createForAction' => [
                            'click $".selector"' => [
                                'statement' => $actionParser->parse('click $".selector"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAction(click $".selector")'
                                    ),
                                ]),
                            ],
                        ],
                    ]),
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        'click $".selector"' => [
                            'statement' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector")'
                                ),
                            ]),
                        ],
                    ]),
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector"' => [
                            'action' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('ActionHandler::handle(click $".selector")'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// DerivedAssertionFactory::createForAction(click $".selector")' . "\n" .
                    '// StatementBlockFactory::create(click $".selector")' . "\n" .
                    '// ActionHandler::handle(click $".selector")'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'two click actions' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector1"',
                        'click $".selector2"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    DerivedAssertionFactory::class => $this->createMockDerivedAssertionFactory([
                        'createForAction' => [
                            'click $".selector1"' => [
                                'statement' => $actionParser->parse('click $".selector1"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAction(click $".selector1")'
                                    ),
                                ]),
                            ],
                            'click $".selector2"' => [
                                'statement' => $actionParser->parse('click $".selector2"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAction(click $".selector2")'
                                    ),
                                ]),
                            ],
                        ],
                    ]),
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
                                ),
                            ]),
                        ],
                        'click $".selector2"' => [
                            'statement' => $actionParser->parse('click $".selector2"'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector2")'
                                ),
                            ]),
                        ],
                    ]),
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector1"' => [
                            'action' => $actionParser->parse('click $".selector1"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('ActionHandler::handle(click $".selector1")'),
                            ]),
                        ],
                        'click $".selector2"' => [
                            'action' => $actionParser->parse('click $".selector2"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('ActionHandler::handle(click $".selector2")'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// DerivedAssertionFactory::createForAction(click $".selector1")' . "\n" .
                    '// StatementBlockFactory::create(click $".selector1")' . "\n" .
                    '// ActionHandler::handle(click $".selector1")' . "\n" .
                    '// DerivedAssertionFactory::createForAction(click $".selector2")' . "\n" .
                    '// StatementBlockFactory::create(click $".selector2")' . "\n" .
                    '// ActionHandler::handle(click $".selector2")'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'single exists assertion' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    DerivedAssertionFactory::class => $this->createMockDerivedAssertionFactory([
                        'createForAssertion' => [
                            '$".selector" exists' => [
                                'statement' => $assertionParser->parse('$".selector" exists'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAssertion($".selector" exists)'
                                    ),
                                ]),
                            ],
                        ],
                    ]),
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'statement' => $assertionParser->parse('$".selector" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector" exists' => [
                            'assertion' => $assertionParser->parse('$".selector" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// DerivedAssertionFactory::createForAssertion($".selector" exists)' . "\n" .
                    '// StatementBlockFactory::create($".selector" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector" exists)'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'two exists assertions' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector1" exists',
                        '$".selector2" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    DerivedAssertionFactory::class => $this->createMockDerivedAssertionFactory([
                        'createForAssertion' => [
                            '$".selector1" exists' => [
                                'statement' => $assertionParser->parse('$".selector1" exists'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAssertion($".selector1" exists)'
                                    ),
                                ]),
                            ],
                            '$".selector2" exists' => [
                                'statement' => $assertionParser->parse('$".selector2" exists'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAssertion($".selector2" exists)'
                                    ),
                                ]),
                            ],
                        ],
                    ]),
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector1" exists' => [
                            'statement' => $assertionParser->parse('$".selector1" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector1" exists' => [
                            'assertion' => $assertionParser->parse('$".selector1" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector1" exists)'),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'assertion' => $assertionParser->parse('$".selector2" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector2" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// DerivedAssertionFactory::createForAssertion($".selector1" exists)' . "\n" .
                    '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    '// DerivedAssertionFactory::createForAssertion($".selector2" exists)' . "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'single click action, single exists assertion' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    DerivedAssertionFactory::class => $this->createMockDerivedAssertionFactory([
                        'createForAction' => [
                            'click $".selector"' => [
                                'statement' => $actionParser->parse('click $".selector"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAction(click $".selector")'
                                    ),
                                ]),
                            ],
                        ],
                        'createForAssertion' => [
                            '$".selector" exists' => [
                                'statement' => $assertionParser->parse('$".selector" exists'),
                                'return' => new CodeBlock([
                                    new SingleLineComment(
                                        'DerivedAssertionFactory::createForAssertion($".selector" exists)'
                                    ),
                                ]),
                            ],
                        ],
                    ]),
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        'click $".selector"' => [
                            'statement' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector")'
                                ),
                            ]),
                        ],
                        '$".selector" exists' => [
                            'statement' => $assertionParser->parse('$".selector" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector"' => [
                            'action' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('ActionHandler::handle(click $".selector")'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector" exists' => [
                            'assertion' => $assertionParser->parse('$".selector" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// DerivedAssertionFactory::createForAction(click $".selector")' . "\n" .
                    '// StatementBlockFactory::create(click $".selector")' . "\n" .
                    '// ActionHandler::handle(click $".selector")' . "\n" .
                    '// DerivedAssertionFactory::createForAssertion($".selector" exists)' . "\n" .
                    '// StatementBlockFactory::create($".selector" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector" exists)'
                ,
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(StepInterface $step, UnsupportedStepException $expectedException)
    {
        $handler = StepHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($step);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();
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
                        $actionParser->parse('click $elements.element_name'),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$elements.element_name'
                        )
                    )
                ),
            ],
            'comparison assertion, examined value is not supported' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$elements.examined is "value"',
                    ],
                ]),
                'expectedException' => new UnsupportedStepException(
                    $stepParser->parse([
                        'assertions' => [
                            '$elements.examined is "value"',
                        ],
                    ]),
                    new UnsupportedStatementException(
                        $assertionParser->parse('$elements.examined is "value"'),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$elements.examined'
                        )
                    )
                ),
            ],
        ];
    }

    public function testHandleAssertionWithUnsupportedIdentifier()
    {
        $stepParser = StepParser::create();
        $assertionParser = AssertionParser::create();

        $step = $stepParser->parse([
            'assertions' => [
                '$elements.examined is "value"',
            ],
        ]);

        $assertion = $assertionParser->parse('$elements.examined is "value"');
        $unsupportedContentException = new UnsupportedContentException(
            UnsupportedContentException::TYPE_IDENTIFIER,
            '$elements.examined'
        );

        $derivedAssertionFactory = \Mockery::mock(DerivedAssertionFactory::class);
        $derivedAssertionFactory
            ->shouldReceive('createForAssertion')
            ->withArgs(function (AssertionInterface $passedAssertion) use ($assertion) {
                $this->assertEquals($assertion, $passedAssertion);

                return true;
            })->andThrow($unsupportedContentException);

        $handler = $this->createStepHandler([
            DerivedAssertionFactory::class => $derivedAssertionFactory,
        ]);

        $this->expectExceptionObject(new UnsupportedStepException(
            $step,
            new UnsupportedStatementException($assertion, $unsupportedContentException)
        ));

        $handler->handle($step);
    }

    /**
     * @param array[] $calls
     *
     * @return DerivedAssertionFactory
     */
    private function createMockDerivedAssertionFactory(array $calls): DerivedAssertionFactory
    {
        $derivedAssertionFactory = \Mockery::mock(DerivedAssertionFactory::class);

        foreach ($calls as $methodName => $methodCalls) {
            if (0 !== count($methodCalls)) {
                $derivedAssertionFactory
                    ->shouldReceive($methodName)
                    ->times(count($methodCalls))
                    ->andReturnUsing(function (StatementInterface $statement) use ($methodCalls) {
                        $data = $methodCalls[$statement->getSource()];

                        $this->assertEquals($data['statement'], $statement);

                        return $data['return'];
                    });
            }
        }

        return $derivedAssertionFactory;
    }

    /**
     * @param array<mixed> $createCalls
     *
     * @return StatementBlockFactory
     */
    private function createMockStatementBlockFactory(array $createCalls): StatementBlockFactory
    {
        $statementBlockFactory = \Mockery::mock(StatementBlockFactory::class);

        if (0 !== count($createCalls)) {
            $statementBlockFactory
                ->shouldReceive('create')
                ->times(count($createCalls))
                ->andReturnUsing(function (StatementInterface $statement) use ($createCalls) {
                    $data = $createCalls[$statement->getSource()];

                    $this->assertEquals($data['statement'], $statement);

                    return $data['return'];
                });
        }

        return $statementBlockFactory;
    }

    /**
     * @param array[] $handleCalls
     *
     * @return ActionHandler
     */
    private function createMockActionHandler(array $handleCalls): ActionHandler
    {
        $actionHandler = \Mockery::mock(ActionHandler::class);

        if (0 !== count($handleCalls)) {
            $actionHandler
                ->shouldReceive('handle')
                ->times(count($handleCalls))
                ->andReturnUsing(function (ActionInterface $action) use ($handleCalls) {
                    $data = $handleCalls[$action->getSource()];

                    $this->assertEquals($data['action'], $action);

                    return $data['return'];
                });
        }

        return $actionHandler;
    }

    /**
     * @param array[] $handleCalls
     *
     * @return AssertionHandler
     */
    private function createMockAssertionHandler(array $handleCalls): AssertionHandler
    {
        $assertionHandler = \Mockery::mock(AssertionHandler::class);

        if (0 !== count($handleCalls)) {
            $assertionHandler
                ->shouldReceive('handle')
                ->times(count($handleCalls))
                ->andReturnUsing(function (AssertionInterface $assertion) use ($handleCalls) {
                    $data = $handleCalls[$assertion->getSource()];

                    $this->assertEquals($data['assertion'], $assertion);

                    return $data['return'];
                });
        }

        return $assertionHandler;
    }

    /**
     * @param array<mixed> $services
     *
     * @return StepHandler
     */
    private function createStepHandler(array $services = []): StepHandler
    {
        $actionHandler = $services[ActionHandler::class] ?? ActionHandler::createHandler();
        $assertionHandler = $services[AssertionHandler::class] ?? AssertionHandler::createHandler();
        $statementBlockFactory = $services[StatementBlockFactory::class] ?? StatementBlockFactory::createFactory();
        $derivedAssertionFactory =
            $services[DerivedAssertionFactory::class] ?? DerivedAssertionFactory::createFactory();

        return new StepHandler(
            $actionHandler,
            $assertionHandler,
            $derivedAssertionFactory,
            $statementBlockFactory
        );
    }
}
