<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;

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
            'click action' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector"' => [
                            'action' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked click $".selector" response'),
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
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAction(\'click $".selector"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'click $".selector"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked click $".selector" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'set action with elemental value' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'set $".selector" to $".value"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    ActionHandler::class => $this->createMockActionHandler([
                        'set $".selector" to $".value"' => [
                            'action' => $actionParser->parse('set $".selector" to $".value"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked set $".selector" to $".value" response'),
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
                    '// $".selector" exists <- set $".selector" to $".value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAction(\'set $".selector" to $".value"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    "\n" .
                    '// $".value" exists <- set $".selector" to $".value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".value" exists\',' . "\n" .
                    '    Statement::createAction(\'set $".selector" to $".value"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".value" exists response' . "\n" .
                    "\n" .
                    '// set $".selector" to $".value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'set $".selector" to $".value"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked set $".selector" to $".value" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'click action, wait action with literal value, wait action with element value' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                        'wait 1',
                        'wait $".duration"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector"' => [
                            'action' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked click $".selector" response'),
                            ]),
                        ],
                        'wait 1' => [
                            'action' => $actionParser->parse('wait 1'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked wait 1 response'),
                            ]),
                        ],
                        'wait $".duration"' => [
                            'action' => $actionParser->parse('wait $".duration"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked wait $".duration" response'),
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
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAction(\'click $".selector"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'click $".selector"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked click $".selector" response' . "\n" .
                    "\n" .
                    '// wait 1' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'wait 1\'' . "\n" .
                    ');' . "\n" .
                    '// mocked wait 1 response' . "\n" .
                    "\n" .
                    '// $".duration" exists <- wait $".duration"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".duration" exists\',' . "\n" .
                    '    Statement::createAction(\'wait $".duration"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".duration" exists response' . "\n" .
                    "\n" .
                    '// wait $".duration"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'wait $".duration"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked wait $".duration" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'non-elemental assertion' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$page.title is "value"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handle' => [
                            '$page.title is "value"' => [
                                'assertion' => $assertionParser->parse('$page.title is "value"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $page.title is "value" response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// $page.title is "value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$page.title is "value"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $page.title is "value" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'exists assertion' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handle' => [
                            '$".selector" exists' => [
                                'assertion' => $assertionParser->parse('$".selector" exists'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $".selector" exists response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $".selector" exists response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'comparison assertion, elemental selector, scalar value' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is "value"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handle' => [
                            '$".selector" is "value"' => [
                                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $".selector" is "value" response'),
                                ]),
                            ],
                        ],
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
                    '// $".selector" exists <- $".selector" is "value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAssertion(\'$".selector" is "value"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    "\n" .
                    '// $".selector" is "value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" is "value"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $".selector" is "value" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'comparison assertion, elemental selector, elemental value' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is $".value"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handle' => [
                            '$".selector" is $".value"' => [
                                'assertion' => $assertionParser->parse('$".selector" is $".value"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $".selector" is $".value" response'),
                                ]),
                            ],
                        ],
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
                    '// $".selector" exists <- $".selector" is $".value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAssertion(\'$".selector" is $".value"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    "\n" .
                    '// $".value" exists <- $".selector" is $".value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".value" exists\',' . "\n" .
                    '    Statement::createAssertion(\'$".selector" is $".value"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".value" exists response' . "\n" .
                    "\n" .
                    '// $".selector" is $".value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" is $".value"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $".selector" is $".value" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'two assertions, no elemental identifiers' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$page.title is "value"',
                        '$page.url is "http://example.com"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handle' => [
                            '$page.title is "value"' => [
                                'assertion' => $assertionParser->parse('$page.title is "value"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $page.title is "value" response'),
                                ]),
                            ],
                            '$page.url is "http://example.com"' => [
                                'assertion' => $assertionParser->parse('$page.url is "http://example.com"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $page.url is "http://example.com" response'),
                                ]),
                            ],
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// $page.title is "value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$page.title is "value"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $page.title is "value" response' . "\n" .
                    "\n" .
                    '// $page.url is "http://example.com"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$page.url is "http://example.com"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $page.url is "http://example.com" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'click action, non-elemental assertion' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                    'assertions' => [
                        '$page.title is "value"',
                    ],
                ]),
                'handler' => $this->createStepHandler([
                    ActionHandler::class => $this->createMockActionHandler([
                        'click $".selector"' => [
                            'action' => $actionParser->parse('click $".selector"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked click $".selector" response'),
                            ]),
                        ],
                    ]),
                    AssertionHandler::class => $this->createMockAssertionHandler([
                        'handle' => [
                            '$page.title is "value"' => [
                                'assertion' => $assertionParser->parse('$page.title is "value"'),
                                'return' => new CodeBlock([
                                    new SingleLineComment('mocked $page.title is "value" response'),
                                ]),
                            ],
                        ],
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
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAction(\'click $".selector"\')' . "\n" .
                    ');' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'click $".selector"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked click $".selector" response' . "\n" .
                    "\n" .
                    '// $page.title is "value"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$page.title is "value"\'' . "\n" .
                    ');' . "\n" .
                    '// mocked $page.title is "value" response' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
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
    private function createStepHandler(array $services = []): StepHandler
    {
        $actionHandler = $services[ActionHandler::class] ?? ActionHandler::createHandler();
        $assertionHandler = $services[AssertionHandler::class] ?? AssertionHandler::createHandler();
        $domIdentifierFactory = $services[DomIdentifierFactory::class] ?? DomIdentifierFactory::createFactory();
        $identifierTypeAnalyser = $services[IdentifierTypeAnalyser::class] ?? IdentifierTypeAnalyser::create();
        $singleQuotedStringEscaper = $services[SingleQuotedStringEscaper::class] ?? SingleQuotedStringEscaper::create();

        return new StepHandler(
            $actionHandler,
            $assertionHandler,
            $domIdentifierFactory,
            $identifierTypeAnalyser,
            $singleQuotedStringEscaper
        );
    }
}
