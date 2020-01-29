<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\Metadata\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class StepHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider handleSuccessDataProvider
     */
    public function testHandleSuccess(
        StepInterface $step,
        CodeBlockInterface $expectedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = StepHandler::createHandler();

        $source = $handler->handle($step);

        $this->assertBlockContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleSuccessDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'empty step' => [
                'step' => $stepParser->parse([]),
                'expectedContent' => new CodeBlock(),
                'expectedMetadata' => new Metadata(),
            ],
            'click action' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists <- click $".selector"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// click $".selector"',
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ ELEMENT }}->click()',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'ELEMENT',
                        VariableNames::STATEMENT,
                    ])),
            ],
            'set action with elemental value' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'set $".selector" to $".value"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists <- set $".selector" to $".value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// $".value" exists <- set $".selector" to $".value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".value" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".value"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// set $".selector" to $".value"',
                    '{{ STATEMENT }} = Statement::createAction(\'set $".selector" to $".value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ VALUE }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".value"}\'))',
                    '{{ VALUE }} = {{ INSPECTOR }}->getValue({{ VALUE }}) ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'COLLECTION',
                        'HAS',
                        'VALUE',
                        VariableNames::STATEMENT,
                    ])),
            ],
            'click action, wait action with literal value, wait action with element value' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                        'wait 1',
                        'wait $".duration"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists <- click $".selector"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// click $".selector"',
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ ELEMENT }}->click()',
                    '',
                    '// wait 1',
                    '{{ STATEMENT }} = Statement::createAction(\'wait 1\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ DURATION }} = "1" ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                    '',
                    '// $".duration" exists <- wait $".duration"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".duration" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".duration"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// wait $".duration"',
                    '{{ STATEMENT }} = Statement::createAction(\'wait $".duration"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ DURATION }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".duration"}\'))',
                    '{{ DURATION }} = {{ INSPECTOR }}->getValue({{ DURATION }}) ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                        'HAS',
                        'ELEMENT',
                        VariableNames::STATEMENT,
                    ])),
            ],
            'non-elemental assertion' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$page.title is "value"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $page.title is "value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                    ])),
            ],
            'exists assertion' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::STATEMENT,
                    ])),
            ],
            'comparison assertion, elemental selector, scalar value' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is "value"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists <- $".selector" is "value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// $".selector" is "value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is "value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                    ])),
            ],
            'comparison assertion, elemental selector, elemental value' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is $".value"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists <- $".selector" is $".value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// $".value" exists <- $".selector" is $".value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".value" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".value"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// $".selector" is $".value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is $".value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".value"}\'))',
                    '{{ EXPECTED }} = {{ INSPECTOR }}->getValue({{ EXPECTED }}) ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ EXAMINED }} = {{ INSPECTOR }}->getValue({{ EXAMINED }}) ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                    ])),
            ],
            'two assertions, no elemental identifiers' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$page.title is "value"',
                        '$page.url is "http://example.com"',
                    ],
                ]),
                'expectedContent' => CodeBlock::fromContent([
                    '// $page.title is "value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                    '// $page.url is "http://example.com"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.url is "http://example.com"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = "http://example.com" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                    ])),
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
                'expectedContent' => CodeBlock::fromContent([
                    '// $".selector" exists <- click $".selector"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{"locator":".selector"}\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '',
                    '// click $".selector"',
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ')',
                    '{{ ELEMENT }}->click()',
                    '',
                    '// $page.title is "value"',
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\')',
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }}',
                    '{{ EXPECTED }} = "value" ?? null',
                    '{{ EXPECTED }} = (string) {{ EXPECTED }}',
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null',
                    '{{ EXAMINED }} = (string) {{ EXAMINED }}',
                    '{{ PHPUNIT }}->assertEquals({{ EXPECTED }}, {{ EXAMINED }})',
                    '',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                        new ClassDependency(Statement::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'ELEMENT',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
                        VariableNames::STATEMENT,
                    ])),
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
}
