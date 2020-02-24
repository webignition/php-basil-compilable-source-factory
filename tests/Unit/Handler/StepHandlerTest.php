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
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\Action\ActionHandler;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierExistenceHandler;
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
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

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
                    AssertionFailureMessageFactory::class => $this->createMockAssertionFailureMessageFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"'
                            ),
                            'message' => '$".selector" exists failure message',
                        ],
                    ]),
                    DomIdentifierExistenceHandler::class => $this->createDomIdentifierExistenceHandler(
                        [
                            '$".selector"' => [
                                'identifier' => new ElementIdentifier('.selector'),
                                'assertionFailureMessage' => '$".selector" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ],
                        []
                    ),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked click $".selector" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                    AssertionFailureMessageFactory::class => $this->createMockAssertionFailureMessageFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $".selector" to $".value"'),
                                '$".selector"'
                            ),
                            'message' => '$".selector" exists failure message',
                        ],
                        '$".value" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('set $".selector" to $".value"'),
                                '$".value"'
                            ),
                            'message' => '$".value" exists failure message',
                        ],
                    ]),
                    DomIdentifierExistenceHandler::class => $this->createDomIdentifierExistenceHandler(
                        [
                        ],
                        [
                            '$".selector"' => [
                                'identifier' => new ElementIdentifier('.selector'),
                                'assertionFailureMessage' => '$".selector" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                            '$".value"' => [
                                'identifier' => new ElementIdentifier('.value'),
                                'assertionFailureMessage' => '$".value" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".value" exists response'),
                                ]),
                            ],
                        ]
                    ),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- set $".selector" to $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".value" exists <- set $".selector" to $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".value" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".value" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// set $".selector" to $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'set $".selector" to $".value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked set $".selector" to $".value" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                    AssertionFailureMessageFactory::class => $this->createMockAssertionFailureMessageFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"'
                            ),
                            'message' => '$".selector" exists failure message',
                        ],
                        '$".duration" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('wait $".duration"'),
                                '$".duration"'
                            ),
                            'message' => '$".duration" exists failure message',
                        ],
                    ]),
                    DomIdentifierExistenceHandler::class => $this->createDomIdentifierExistenceHandler(
                        [
                            '$".selector"' => [
                                'identifier' => new ElementIdentifier('.selector'),
                                'assertionFailureMessage' => '$".selector" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ],
                        [
                            '$".duration"' => [
                                'identifier' => new ElementIdentifier('.duration'),
                                'assertionFailureMessage' => '$".duration" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".duration" exists response'),
                                ]),
                            ],
                        ]
                    ),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked click $".selector" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// wait 1' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'wait 1\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked wait 1 response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".duration" exists <- wait $".duration"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".duration" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".duration" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// wait $".duration"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'wait $".duration"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked wait $".duration" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                        '$page.title is "value"' => [
                            'assertion' => $assertionParser->parse('$page.title is "value"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked $page.title is "value" response'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// $page.title is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $page.title is "value" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                        '$".selector" exists' => [
                            'assertion' => $assertionParser->parse('$".selector" exists'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked $".selector" exists response'),
                            ]),
                        ],
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                        '$".selector" is "value"' => [
                            'assertion' => $assertionParser->parse('$".selector" is "value"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked $".selector" is "value" response'),
                            ]),
                        ],
                    ]),
                    AssertionFailureMessageFactory::class => $this->createMockAssertionFailureMessageFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$".selector" is "value"'),
                                '$".selector"'
                            ),
                            'message' => '$".selector" exists failure message',
                        ],
                    ]),
                    DomIdentifierExistenceHandler::class => $this->createDomIdentifierExistenceHandler(
                        [
                        ],
                        [
                            '$".selector"' => [
                                'identifier' => new ElementIdentifier('.selector'),
                                'assertionFailureMessage' => '$".selector" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ]
                    ),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- $".selector" is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".selector" is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $".selector" is "value" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                        '$".selector" is $".value"' => [
                            'assertion' => $assertionParser->parse('$".selector" is $".value"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked $".selector" is $".value" response'),
                            ]),
                        ],
                    ]),
                    AssertionFailureMessageFactory::class => $this->createMockAssertionFailureMessageFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$".selector" is $".value"'),
                                '$".selector"'
                            ),
                            'message' => '$".selector" exists failure message',
                        ],
                        '$".value" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $assertionParser->parse('$".selector" is $".value"'),
                                '$".value"'
                            ),
                            'message' => '$".value" exists failure message',
                        ],
                    ]),
                    DomIdentifierExistenceHandler::class => $this->createDomIdentifierExistenceHandler(
                        [
                        ],
                        [
                            '$".selector"' => [
                                'identifier' => new ElementIdentifier('.selector'),
                                'assertionFailureMessage' => '$".selector" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                            '$".value"' => [
                                'identifier' => new ElementIdentifier('.value'),
                                'assertionFailureMessage' => '$".value" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".value" exists response'),
                                ]),
                            ],
                        ]
                    ),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- $".selector" is $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".value" exists <- $".selector" is $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".value" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".value" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".selector" is $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is $".value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $".selector" is $".value" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                    ]),
                ]),
                'expectedRenderedSource' =>
                    '// $page.title is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $page.title is "value" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $page.url is "http://example.com"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.url is "http://example.com"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $page.url is "http://example.com" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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
                        '$page.title is "value"' => [
                            'assertion' => $assertionParser->parse('$page.title is "value"'),
                            'return' => new CodeBlock([
                                new SingleLineComment('mocked $page.title is "value" response'),
                            ]),
                        ],
                    ]),
                    AssertionFailureMessageFactory::class => $this->createMockAssertionFailureMessageFactory([
                        '$".selector" exists' => [
                            'assertion' => new DerivedElementExistsAssertion(
                                $actionParser->parse('click $".selector"'),
                                '$".selector"'
                            ),
                            'message' => '$".selector" exists failure message',
                        ],
                    ]),
                    DomIdentifierExistenceHandler::class => $this->createDomIdentifierExistenceHandler(
                        [
                            '$".selector"' => [
                                'identifier' => new ElementIdentifier('.selector'),
                                'assertionFailureMessage' => '$".selector" exists failure message',
                                'return' => new CodeBlock([
                                    new SingleLineComment('derived $".selector" exists response'),
                                ]),
                            ],
                        ],
                        []
                    ),
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// derived $".selector" exists response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked click $".selector" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $page.title is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '// mocked $page.title is "value" response' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::STATEMENT,
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

    private function createMockAssertionHandler(array $handleCalls): AssertionHandler
    {
        $assertionHandler = \Mockery::mock(AssertionHandler::class);

        if (0 !== count($handleCalls)) {
            $assertionHandler
                ->shouldReceive('handle')
                ->times(count($handleCalls))
                ->andReturnUsing(function (AssertionInterface $action) use ($handleCalls) {
                    $data = $handleCalls[$action->getSource()];

                    $this->assertEquals($data['assertion'], $action);

                    return $data['return'];
                });
        }

        return $assertionHandler;
    }

    private function createMockAssertionFailureMessageFactory(array $createForAssertionCalls)
    {
        $assertionFailureMessageFactory = \Mockery::mock(AssertionFailureMessageFactory::class);

        $assertionFailureMessageFactory
            ->shouldReceive('createForAssertion')
            ->times(count($createForAssertionCalls))
            ->andReturnUsing(function (DerivedElementExistsAssertion $assertion) use ($createForAssertionCalls) {
                $data = $createForAssertionCalls[$assertion->getSource()];

                $this->assertEquals($data['assertion'], $assertion);

                return $data['message'];
            });

        return $assertionFailureMessageFactory;
    }

    private function createDomIdentifierExistenceHandler(
        array $createForElementCalls,
        array $createForCollectionCalls
    ): DomIdentifierExistenceHandler {
        $domIdentifierExistenceHandler = \Mockery::mock(DomIdentifierExistenceHandler::class);

        $createForElementCallCount = count($createForElementCalls);
        if ($createForElementCallCount > 0) {
            $domIdentifierExistenceHandler
                ->shouldReceive('createForElement')
                ->times($createForElementCallCount)
                ->andReturnUsing(function (
                    ElementIdentifierInterface $identifier,
                    string $assertionFailureMessage
                ) use (
                    $createForElementCalls
                ) {
                    $data = $createForElementCalls[(string) $identifier];

                    $this->assertEquals($data['identifier'], $identifier);
                    $this->assertSame($data['assertionFailureMessage'], $assertionFailureMessage);

                    return $data['return'];
                });
        }

        $createForCollectionCallCount = count($createForCollectionCalls);
        if ($createForCollectionCallCount > 0) {
            $domIdentifierExistenceHandler
                ->shouldReceive('createForCollection')
                ->times($createForCollectionCallCount)
                ->andReturnUsing(function (
                    ElementIdentifierInterface $identifier,
                    string $assertionFailureMessage
                ) use (
                    $createForCollectionCalls
                ) {
                    $data = $createForCollectionCalls[(string) $identifier];

                    $this->assertEquals($data['identifier'], $identifier);
                    $this->assertSame($data['assertionFailureMessage'], $assertionFailureMessage);

                    return $data['return'];
                });
        }

        return $domIdentifierExistenceHandler;
    }

    private function createStepHandler(array $services = []): StepHandler
    {
        $actionHandler = $services[ActionHandler::class] ?? ActionHandler::createHandler();
        $assertionHandler = $services[AssertionHandler::class] ?? AssertionHandler::createHandler();
        $domIdentifierExistenceHandler =
            $services[DomIdentifierExistenceHandler::class] ?? DomIdentifierExistenceHandler::createHandler();
        $domIdentifierFactory = $services[DomIdentifierFactory::class] ?? DomIdentifierFactory::createFactory();
        $identifierTypeAnalyser = $services[IdentifierTypeAnalyser::class] ?? IdentifierTypeAnalyser::create();
        $singleQuotedStringEscaper = $services[SingleQuotedStringEscaper::class] ?? SingleQuotedStringEscaper::create();
        $assertionFailureMessageFactory =
            $services[AssertionFailureMessageFactory::class] ?? AssertionFailureMessageFactory::createFactory();

        return new StepHandler(
            $actionHandler,
            $assertionHandler,
            $domIdentifierExistenceHandler,
            $domIdentifierFactory,
            $identifierTypeAnalyser,
            $singleQuotedStringEscaper,
            $assertionFailureMessageFactory
        );
    }
}
