<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\ClassName;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierNotExistsAssertionDataProviderTrait
{
    public function createFromIdentifierNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
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
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
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
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
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
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
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
