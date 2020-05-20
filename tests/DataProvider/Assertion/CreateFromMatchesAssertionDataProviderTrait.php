<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromMatchesAssertionDataProviderTrait
{
    public function createFromMatchesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'matches comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" matches "/^value/"'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExpectedValue("/^value/" ?? null);' . "\n" .
                    '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})());' . "\n" .
                    '{{ PHPUNIT }}->assertRegExp(' . "\n" .
                    '    {{ PHPUNIT }}->getExpectedValue(),' . "\n" .
                    '    {{ PHPUNIT }}->getExaminedValue()' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name matches "/^value/"'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExpectedValue("/^value/" ?? null);' . "\n" .
                    '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})());' . "\n" .
                    '{{ PHPUNIT }}->assertRegExp(' . "\n" .
                    '    {{ PHPUNIT }}->getExpectedValue(),' . "\n" .
                    '    {{ PHPUNIT }}->getExaminedValue()' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
