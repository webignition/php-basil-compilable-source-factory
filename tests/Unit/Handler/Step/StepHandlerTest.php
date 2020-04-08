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
use webignition\BasilCompilableSourceFactory\Handler\Step\FooDerivedAssertionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
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
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'statement' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector" exists)'
                                ),
                            ]),
                        ],
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
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// StatementBlockFactory::create($".selector" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector")' . "\n" .
                    '// ActionHandler::handle(click $".selector")' . "\n"
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
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector1" exists' => [
                            'statement' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector1"'),
                                '$".selector1"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector2"'),
                                '$".selector2"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
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
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector1" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector1"'),
                                '$".selector1"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector1" exists)'),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector2"'),
                                '$".selector2"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".selector2" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector1")' . "\n" .
                    '// ActionHandler::handle(click $".selector1")' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector2")' . "\n" .
                    '// ActionHandler::handle(click $".selector2")' . "\n"
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
                    '// StatementBlockFactory::create($".selector" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector" exists)' . "\n"
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'single exists assertion, descendant identifier' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$"{{ $".parent" }} .child" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'statement' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                                '$".parent"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" exists)'
                                ),
                            ]),
                        ],
                        '$"{{ $".parent" }} .child" exists' => [
                            'statement' => $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($"{{ $".parent" }} .child" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".parent" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                                '$".parent"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($".parent" exists)'),
                            ]),
                        ],
                        '$"{{ $".parent" }} .child" exists' => [
                            'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment('AssertionHandler::handle($"{{ $".parent" }} .child" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// StatementBlockFactory::create($".parent" exists)' . "\n" .
                    '// AssertionHandler::handle($".parent" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($"{{ $".parent" }} .child" exists)' . "\n" .
                    '// AssertionHandler::handle($"{{ $".parent" }} .child" exists)' . "\n"

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
                    '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)' . "\n"
                ,
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
                'handler' => $this->createStepHandler([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector1" exists' => [
                            'statement' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector1"'),
                                '$".selector1"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        'click $".selector1"' => [
                            'statement' => $actionParser->parse('click $".selector1"'),
                            'return' => new CodeBlock([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
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
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector1"' => [
                            'action' => $actionParser->parse('click $".selector1"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('ActionHandler::handle(click $".selector1")'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector1" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector1"'),
                                '$".selector1"'
                            ),
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
                    '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector1")' . "\n" .
                    '// ActionHandler::handle(click $".selector1")' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)' . "\n"
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

        $handler = $this->createStepHandler();

        $this->expectExceptionObject(new UnsupportedStepException(
            $step,
            new UnsupportedStatementException($assertion, $unsupportedContentException)
        ));

        $handler->handle($step);
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
        $fooDerivedAssertionFactory =
            $services[FooDerivedAssertionFactory::class] ?? FooDerivedAssertionFactory::createFactory();

        return new StepHandler(
            $actionHandler,
            $assertionHandler,
            $statementBlockFactory,
            $fooDerivedAssertionFactory
        );
    }
}
