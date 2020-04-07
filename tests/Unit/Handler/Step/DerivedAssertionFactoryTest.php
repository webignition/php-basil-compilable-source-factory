<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StepHandler;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;

class DerivedAssertionFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateFactory()
    {
        $this->assertInstanceOf(DerivedAssertionFactory::class, DerivedAssertionFactory::createFactory());
    }

    /**
     * @dataProvider createForActionDataProvider
     */
    public function testCreateForAction(
        ActionInterface $action,
        DerivedAssertionFactory $factory,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $codeBlock = $factory->createForAction($action);

        $this->assertEquals($expectedRenderedContent, $codeBlock->render());
        $this->assertEquals($expectedMetadata, $codeBlock->getMetadata());
    }

    public function createForActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'click action' => [
                'action' => $actionParser->parse('click $".selector"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".selector" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsElement' => [
                            '$".selector" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('click $".selector"'),
                                    '$".selector"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".selector" exists statement block' . "\n" .
                    '// derived $".selector" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'click action, descendant identifier' => [
                'action' => $actionParser->parse('click $"{{ $".parent" }} .child"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $"{{ $".parent" }} .child"'),
                                '$".parent"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".parent" exists statement block'),
                            ]),
                        ],
                        '$"{{ $".parent" }} .child" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $"{{ $".parent" }} .child"'),
                                '$"{{ $".parent" }} .child"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $"{{ $".parent" }} .child" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsElement' => [
                            '$".parent" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('click $"{{ $".parent" }} .child"'),
                                    '$".parent"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".parent" exists response'),
                                ]),
                            ],
                            '$"{{ $".parent" }} .child" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('click $"{{ $".parent" }} .child"'),
                                    '$"{{ $".parent" }} .child"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $"{{ $".parent" }} .child" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".parent" exists statement block' . "\n" .
                    '// derived $".parent" exists response' . "\n" .
                    '// derived $"{{ $".parent" }} .child" exists statement block' . "\n" .
                    '// derived $"{{ $".parent" }} .child" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'set action' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $".selector" to "value"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".selector" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".selector" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('set $".selector" to "value"'),
                                    '$".selector"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".selector" exists statement block' . "\n" .
                    '// derived $".selector" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'set action, descendant identifier' => [
                'action' => $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                                '$".parent"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".parent" exists statement block'),
                            ]),
                        ],
                        '$"{{ $".parent" }} .child" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                                '$"{{ $".parent" }} .child"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $"{{ $".parent" }} .child" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".parent" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                                    '$".parent"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".parent" exists response'),
                                ]),
                            ],
                            '$"{{ $".parent" }} .child" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                                    '$"{{ $".parent" }} .child"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $"{{ $".parent" }} .child" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".parent" exists statement block' . "\n" .
                    '// derived $".parent" exists response' . "\n" .
                    '// derived $"{{ $".parent" }} .child" exists statement block' . "\n" .
                    '// derived $"{{ $".parent" }} .child" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'set action, elemental value' => [
                'action' => $actionParser->parse('set $".selector" to $".value"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $".selector" to $".value"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".selector" exists statement block'),
                            ]),
                        ],
                        '$".value" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $".selector" to $".value"'),
                                '$".value"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".value" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".selector" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('set $".selector" to $".value"'),
                                    '$".selector"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                            '$".value" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('set $".selector" to $".value"'),
                                    '$".value"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".value" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".selector" exists statement block' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '// derived $".value" exists statement block' . "\n" .
                    '// derived $".value" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'wait action, elemental duration' => [
                'action' => $actionParser->parse('wait $".duration"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".duration" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('wait $".duration"'),
                                '$".duration"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".duration" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".duration" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $actionParser->parse('wait $".duration"'),
                                    '$".duration"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".duration" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".duration" exists statement block' . "\n" .
                    '// derived $".duration" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    /**
     * @dataProvider createForAssertionDataProvider
     */
    public function testCreateForAssertion(
        AssertionInterface $assertion,
        DerivedAssertionFactory $factory,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $codeBlock = $factory->createForAssertion($assertion);

        $this->assertEquals($expectedRenderedContent, $codeBlock->render());
        $this->assertEquals($expectedMetadata, $codeBlock->getMetadata());
    }

    public function createForAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'factory' => $this->createFactory(),
                'expectedRenderedContent' => '',
                'expectedMetadata' => new Metadata(),
            ],
            'not-exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'factory' => $this->createFactory(),
                'expectedRenderedContent' => '',
                'expectedMetadata' => new Metadata(),
            ],
            'exists assertion, descendant identifier' => [
                'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".parent" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                                '$".parent"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".parent" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".parent" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                                    '$".parent"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".parent" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".parent" exists statement block' . "\n" .
                    '// derived $".parent" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'is assertion' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$".selector" is "value"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".selector" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".selector" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $assertionParser->parse('$".selector" is "value"'),
                                    '$".selector"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".selector" exists statement block' . "\n" .
                    '// derived $".selector" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
            'is assertion, elemental value' => [
                'assertion' => $assertionParser->parse('$".selector" is $".value"'),
                'factory' => $this->createFactory([
                    StatementBlockFactory::class => $this->createMockStatementBlockFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$".selector" is $".value"'),
                                '$".selector"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".selector" exists statement block'),
                            ]),
                        ],
                        '$".value" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$".selector" is $".value"'),
                                '$".value"'
                            ),
                            'return' => new CodeBlock([
                                new SingleLineComment('derived $".value" exists statement block'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handleExistenceAssertionAsCollection' => [
                            '$".selector" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $assertionParser->parse('$".selector" is $".value"'),
                                    '$".selector"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                            '$".value" exists' => [
                                'assertion' => new DerivedElementExistsAssertion(
                                    $assertionParser->parse('$".selector" is $".value"'),
                                    '$".value"'
                                ),
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".value" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// derived $".selector" exists statement block' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '// derived $".value" exists statement block' . "\n" .
                    '// derived $".value" exists response'
                ,
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }

    public function testCreateForActionThrowsException()
    {
        $action = new InteractionAction(
            'click "foo"',
            'click',
            '"foo"',
            '"foo"'
        );

        $this->expectExceptionObject(
            new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, '"foo"')
        );

        $factory = DerivedAssertionFactory::createFactory();
        $factory->createForAction($action);
    }

    public function testCreateForAssertionThrowsException()
    {
        $assertion = new ComparisonAssertion(
            '"foo" is "value"',
            '"foo"',
            'is',
            '"value"'
        );

        $this->expectExceptionObject(
            new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, '"foo"')
        );

        $factory = DerivedAssertionFactory::createFactory();
        $factory->createForAssertion($assertion);
    }

    private function createMockStatementBlockFactory(array $createCalls): StatementBlockFactory
    {
        $statementBlockFactory = \Mockery::mock(StatementBlockFactory::class);

        if (0 !== count($createCalls)) {
            $statementBlockFactory
                ->shouldReceive('create')
                ->times(count($createCalls))
                ->andReturnUsing(function (AssertionInterface $assertion) use ($createCalls) {
                    $data = $createCalls[$assertion->getSource()];

                    $this->assertEquals($data['assertion'], $assertion);

                    return $data['return'];
                });
        }

        return $statementBlockFactory;
    }

    /**
     * @param array[] $calls
     *
     * @return AssertionHandler
     */
    private function createMockAssertionHandler(array $calls): AssertionHandler
    {
        $assertionHandler = \Mockery::mock(AssertionHandler::class);

        foreach ($calls as $methodName => $methodCalls) {
            if (0 !== count($methodCalls)) {
                $assertionHandler
                    ->shouldReceive($methodName)
                    ->times(count($methodCalls))
                    ->andReturnUsing(function (AssertionInterface $assertion) use ($methodCalls) {
                        $data = $methodCalls[$assertion->getSource()];

                        $this->assertEquals($data['assertion'], $assertion);

                        return $data['return'];
                    });
            }
        }

        return $assertionHandler;
    }

    /**
     * @param array<mixed> $services
     *
     * @return StepHandler
     */
    private function createFactory(array $services = []): DerivedAssertionFactory
    {
        $assertionHandler = $services[AssertionHandler::class] ?? AssertionHandler::createHandler();
        $domIdentifierFactory = $services[DomIdentifierFactory::class] ?? DomIdentifierFactory::createFactory();
        $identifierTypeAnalyser = $services[IdentifierTypeAnalyser::class] ?? IdentifierTypeAnalyser::create();
        $statementBlockFactory = $services[StatementBlockFactory::class] ?? StatementBlockFactory::createFactory();

        return new DerivedAssertionFactory(
            $assertionHandler,
            $domIdentifierFactory,
            $identifierTypeAnalyser,
            $statementBlockFactory
        );
    }
}
