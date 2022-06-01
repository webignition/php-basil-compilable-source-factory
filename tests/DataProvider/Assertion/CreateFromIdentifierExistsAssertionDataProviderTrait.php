<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createFromIdentifierExistsAssertionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExaminedValue(((function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return $element->getAttribute(\'attribute_name\');' . "\n" .
                    '})() ?? null) !== null);' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a[href=foo.html]"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": "a[href=foo.html]"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExaminedValue(((function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": "a[href=foo.html]"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return $element->getAttribute(\'attribute_name\');' . "\n" .
                    '})() ?? null) !== null);' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, click action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, submit action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('submit $".selector"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, set action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('set $".selector" to "value"'),
                    '$".selector"',
                    'exists'
                ),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived exists comparison, wait action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('wait $".duration"'),
                    '$".duration"',
                    'exists'
                ),
                'expectedRenderedSource' => '{{ PHPUNIT }}->examinedElementIdentifier = ' .
                    'ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".duration"' . "\n" .
                    '}\');' . "\n" .
                    'try {' . "\n" .
                    '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '        {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    '    );' . "\n" .
                    '} catch (InvalidLocatorException $exception) {' . "\n" .
                    '    self::staticSetLastException($exception);' . "\n" .
                    '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n" .
                    '}' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassName(ElementIdentifier::class),
                        new ClassName(InvalidLocatorException::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
