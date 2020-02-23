<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class StepHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider handleSuccessDataProvider
     */
    public function testHandleSuccess(
        StepInterface $step,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $handler = StepHandler::createHandler();

        $source = $handler->handle($step);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function handleSuccessDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'empty step' => [
                'step' => $stepParser->parse([]),
                'expectedRenderedSource' => '',
                'expectedMetadata' => new Metadata(),
            ],
            'click action' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ ELEMENT }}->click();' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                        'ELEMENT',
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
                'expectedRenderedSource' =>
                    '// $".selector" exists <- set $".selector" to $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".value" exists <- set $".selector" to $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".value" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".value"}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".value\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".value\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// set $".selector" to $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'set $".selector" to $".value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ VALUE }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".value"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }});' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'COLLECTION',
                        'HAS',
                        'VALUE',
                        VariableNames::STATEMENT,
                        'ELEMENT',
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
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ ELEMENT }}->click();' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// wait 1' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'wait 1\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ DURATION }} = (int) ("1" ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".duration" exists <- wait $".duration"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".duration" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".duration"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".duration\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".duration\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// wait $".duration"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'wait $".duration"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ DURATION }} = (int) ((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".duration"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})() ?? 0);' . "\n" .
                    'usleep({{ DURATION }} * 1000);' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'DURATION',
                        'HAS',
                        'ELEMENT',
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
                'expectedRenderedSource' =>
                    '// $page.title is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ EXPECTED }},' . "\n" .
                    '    {{ EXAMINED }}' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
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
                'expectedRenderedSource' =>
                    '// $".selector" exists' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
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
                'expectedRenderedSource' =>
                    '// $".selector" exists <- $".selector" is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".selector" is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ EXPECTED }},' . "\n" .
                    '    {{ EXAMINED }}' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                        'ELEMENT',
                    ]),
                ]),
            ],
            'comparison assertion, elemental selector, elemental value' => [
                'step' => $stepParser->parse([
                    'assertions' => [
                        '$".selector" is $".value"',
                    ],
                ]),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- $".selector" is $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->has(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".value" exists <- $".selector" is $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".value" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{"locator":".value"}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".value\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".value\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $".selector" is $".value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" is $".value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXPECTED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".value"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ EXPECTED }},' . "\n" .
                    '    {{ EXAMINED }}' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'HAS',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::STATEMENT,
                        'ELEMENT',
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
                'expectedRenderedSource' =>
                    '// $page.title is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ EXPECTED }},' . "\n" .
                    '    {{ EXAMINED }}' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $page.url is "http://example.com"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.url is "http://example.com"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXPECTED }} = "http://example.com" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ CLIENT }}->getCurrentURL() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ EXPECTED }},' . "\n" .
                    '    {{ EXAMINED }}' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
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
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$".selector" exists\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// click $".selector"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAction(\'click $".selector"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    '{{ ELEMENT }}->click();' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n" .
                    "\n" .
                    '// $page.title is "value"' . "\n" .
                    '{{ STATEMENT }} = Statement::createAssertion(\'$page.title is "value"\');' . "\n" .
                    '{{ PHPUNIT }}->currentStatement = {{ STATEMENT }};' . "\n" .
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = {{ CLIENT }}->getTitle() ?? null;' . "\n" .
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    {{ EXPECTED }},' . "\n" .
                    '    {{ EXAMINED }}' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->completedStatements[] = {{ STATEMENT }};' . "\n"
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                        VariableNames::EXAMINED_VALUE,
                        VariableNames::EXPECTED_VALUE,
                        'HAS',
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
}
