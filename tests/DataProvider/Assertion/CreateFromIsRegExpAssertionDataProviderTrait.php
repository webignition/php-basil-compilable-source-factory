<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\ResolvablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\DerivedValueOperationAssertion;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromIsRegExpAssertionDataProviderTrait
{
    public function createFromIsRegExpAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'derived is-regexp, matches assertion with scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^value/"'),
                    '"/^value/"',
                    'is-regexp'
                ),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExaminedValue("/^value/");' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExpectedValue(' . "\n" .
                    '    @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExpectedValue()' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'derived is-regexp, matches assertion with elemental value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"'),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".pattern-container"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue($element);' . "\n" .
                    '})());' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExpectedValue(' . "\n" .
                    '    @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExpectedValue()' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'derived is-regexp, matches assertion with attribute value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name'),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".pattern-container"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return $element->getAttribute(\'attribute_name\');' . "\n" .
                    '})());' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExpectedValue(' . "\n" .
                    '    @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExpectedValue()' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
