<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\ResolvablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromNotExistsAssertionDataProviderTrait
{
    public function createFromNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '    {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
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
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\');' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExaminedValue(' . "\n" .
                    '    {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->setBooleanExaminedValue(((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})() ?? null) !== null);' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n" .
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
                    Metadata::KEY_VARIABLE_EXPORTS => ResolvablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
