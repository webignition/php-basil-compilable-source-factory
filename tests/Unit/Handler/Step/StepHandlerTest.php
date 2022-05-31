<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTest;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\StatementInterface;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\StubbleResolvable\ResolvableInterface;

class StepHandlerTest extends AbstractResolvableTest
{
    /**
     * @dataProvider handleSuccessDataProvider
     */
    public function testHandleSuccess(
        StepInterface $step,
        StepHandler $handler,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ): void {
        $source = $handler->handle($step);

        $this->assertRenderResolvable($expectedRenderedContent, $source);
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @return array<mixed>
     */
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
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector"'),
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
                            'statement' => $actionParser->parse('click $".selector"'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector")'
                                ),
                            ]),
                        ],
                    ]),
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector"' => [
                            'action' => $actionParser->parse('click $".selector"'),
                            'return' => new Body([
                                new SingleLineComment('ActionHandler::handle(click $".selector")'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector" exists' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".selector" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector")' . "\n" .
                    '// ActionHandler::handle(click $".selector")' . "\n",
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
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"'),
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
                            'statement' => $actionParser->parse('click $".selector1"'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector2"'),
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
                            'statement' => $actionParser->parse('click $".selector2"'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector2")'
                                ),
                            ]),
                        ],
                    ]),
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector1"' => [
                            'action' => $actionParser->parse('click $".selector1"'),
                            'return' => new Body([
                                new SingleLineComment('ActionHandler::handle(click $".selector1")'),
                            ]),
                        ],
                        'click $".selector2"' => [
                            'action' => $actionParser->parse('click $".selector2"'),
                            'return' => new Body([
                                new SingleLineComment('ActionHandler::handle(click $".selector2")'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector1" exists' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"'),
                                '$".selector1"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector1" exists)'),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector2"'),
                                '$".selector2"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector2" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector1")' . "\n" .
                    '// ActionHandler::handle(click $".selector1")' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector2")' . "\n" .
                    '// ActionHandler::handle(click $".selector2")' . "\n",
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
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector" exists' => [
                            'assertion' => $assertionParser->parse('$".selector" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".selector" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector" exists)' . "\n",
                'expectedMetadata' => new Metadata(),
            ],
            'single exists assertion, descendant identifier' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".parent" >> $".child" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child" exists'),
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
                            'statement' => $assertionParser->parse('$".parent" >> $".child" exists'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" >> $".child" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".parent" exists' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child" exists'),
                                '$".parent"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".parent" exists)'),
                            ]),
                        ],
                        '$".parent" >> $".child" exists' => [
                            'assertion' => $assertionParser->parse('$".parent" >> $".child" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".parent" >> $".child" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".parent" exists)' . "\n" .
                    '// AssertionHandler::handle($".parent" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".parent" >> $".child" exists)' . "\n" .
                    '// AssertionHandler::handle($".parent" >> $".child" exists)' . "\n",
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
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector1" exists)'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector1" exists' => [
                            'assertion' => $assertionParser->parse('$".selector1" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector1" exists)'),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'assertion' => $assertionParser->parse('$".selector2" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector2" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)' . "\n",
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
                            'statement' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"'),
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
                            'statement' => $actionParser->parse('click $".selector1"'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create(click $".selector1")'
                                ),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'statement' => $assertionParser->parse('$".selector2" exists'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".selector2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector1"' => [
                            'action' => $actionParser->parse('click $".selector1"'),
                            'return' => new Body([
                                new SingleLineComment('ActionHandler::handle(click $".selector1")'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".selector1" exists' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $actionParser->parse('click $".selector1"'),
                                '$".selector1"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector1" exists)'),
                            ]),
                        ],
                        '$".selector2" exists' => [
                            'assertion' => $assertionParser->parse('$".selector2" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".selector2" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".selector1" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create(click $".selector1")' . "\n" .
                    '// ActionHandler::handle(click $".selector1")' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".selector2" exists)' . "\n" .
                    '// AssertionHandler::handle($".selector2" exists)' . "\n",
                'expectedMetadata' => new Metadata(),
            ],
            'two descendant exists assertions with common parent' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".parent" >> $".child1" exists',
                        '$".parent" >> $".child2" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child1" exists'),
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
                            'statement' => $assertionParser->parse('$".parent" >> $".child1" exists'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" >> $".child1" exists)'
                                ),
                            ]),
                        ],
                        '$".parent" >> $".child2" exists' => [
                            'statement' => $assertionParser->parse('$".parent" >> $".child2" exists'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($".parent" >> $".child2" exists)'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '$".parent" exists' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$".parent" >> $".child1" exists'),
                                '$".parent"',
                                'exists'
                            ),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".parent" exists)'),
                            ]),
                        ],
                        '$".parent" >> $".child1" exists' => [
                            'assertion' => $assertionParser->parse('$".parent" >> $".child1" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".parent" >> $".child1" exists)'),
                            ]),
                        ],
                        '$".parent" >> $".child2" exists' => [
                            'assertion' => $assertionParser->parse('$".parent" >> $".child2" exists'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($".parent" >> $".child2" exists)'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create($".parent" exists)' . "\n" .
                    '// AssertionHandler::handle($".parent" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".parent" >> $".child1" exists)' . "\n" .
                    '// AssertionHandler::handle($".parent" >> $".child1" exists)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($".parent" >> $".child2" exists)' . "\n" .
                    '// AssertionHandler::handle($".parent" >> $".child2" exists)' . "\n",
                'expectedMetadata' => new Metadata(),
            ],
            'derived is-regexp' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$page.title matches "/pattern/"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '"/pattern/" is-regexp' => [
                            'statement' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$page.title matches "/pattern/"'),
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
                            'statement' => $assertionParser->parse('$page.title matches "/pattern/"'),
                            'return' => new Body([
                                new SingleLineComment(
                                    'StatementBlockFactory::create($page.title matches "/pattern/")'
                                ),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        '"/pattern/" is-regexp' => [
                            'assertion' => new DerivedValueOperationAssertion(
                                $assertionParser->parse('$page.title matches "/pattern/"'),
                                '"/pattern/"',
                                'is-regexp'
                            ),
                            'return' => new Body([
                                new SingleLineComment(
                                    'AssertionHandler::handle("/pattern/" is-regexp)'
                                ),
                            ]),
                        ],
                        '$page.title matches "/pattern/"' => [
                            'assertion' => $assertionParser->parse('$page.title matches "/pattern/"'),
                            'return' => new Body([
                                new SingleLineComment('AssertionHandler::handle($page.title matches "/pattern/")'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' => '// StatementBlockFactory::create("/pattern/" is-regexp)' . "\n" .
                    '// AssertionHandler::handle("/pattern/" is-regexp)' . "\n" .
                    "\n" .
                    '// StatementBlockFactory::create($page.title matches "/pattern/")' . "\n" .
                    '// AssertionHandler::handle($page.title matches "/pattern/")' . "\n",
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(StepInterface $step, UnsupportedStepException $expectedException): void
    {
        $handler = StepHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($step);
    }

    /**
     * @return array<mixed>
     */
    public function handleThrowsExceptionDataProvider(): array
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
                        $actionParser->parse('click $elements.element_name'),
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
            })
            ->andThrow($unsupportedContentException)
        ;

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
     * @param array<string, array{"statement": StatementInterface, "return": ResolvableInterface}> $createCalls
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
                })
            ;
        }

        return $statementBlockFactory;
    }

    /**
     * @param array<string, array{"action": ActionInterface, "return": ResolvableInterface}> $handleCalls
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
                })
            ;
        }

        return $actionHandler;
    }

    /**
     * @param array<string, array{"assertion": AssertionInterface, "return": ResolvableInterface}> $handleCalls
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
                })
            ;
        }

        return $assertionHandler;
    }

    /**
     * @param array<mixed> $services
     */
    private function createStepHandler(array $services = []): StepHandler
    {
        $actionHandler = $services[ActionHandler::class] ?? null;
        $actionHandler = $actionHandler instanceof ActionHandler ? $actionHandler : ActionHandler::createHandler();

        $assertionHandler = $services[AssertionHandler::class] ?? null;
        $assertionHandler = $assertionHandler instanceof AssertionHandler
            ? $assertionHandler
            : AssertionHandler::createHandler();

        $statementBlockFactory = $services[StatementBlockFactory::class] ?? null;
        $statementBlockFactory = $statementBlockFactory instanceof StatementBlockFactory
            ? $statementBlockFactory
            : StatementBlockFactory::createFactory();

        $derivedAssertionFactory = $services[DerivedAssertionFactory::class] ?? null;
        $derivedAssertionFactory = $derivedAssertionFactory instanceof DerivedAssertionFactory
            ? $derivedAssertionFactory
            : DerivedAssertionFactory::createFactory();

        return new StepHandler($actionHandler, $assertionHandler, $statementBlockFactory, $derivedAssertionFactory);
    }
}
