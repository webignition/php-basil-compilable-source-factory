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

trait CreateFromIncludesAssertionDataProviderTrait
{
    public function createFromIncludesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'includes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" includes "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->find(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue({{ ELEMENT }});' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertStringContainsString((string) {{ EXPECTED }}, (string) {{ EXAMINED }});'
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                    ]),
                ]),
            ],
            'includes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name includes "value"'),
                'expectedRenderedSource' =>
                    '{{ EXPECTED }} = "value" ?? null;' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(' .
                    'ElementIdentifier::fromJson(\'{"locator":".selector"}\')' .
                    ');' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->assertStringContainsString((string) {{ EXPECTED }}, (string) {{ EXAMINED }});'
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
                        VariableNames::EXPECTED_VALUE,
                        VariableNames::EXAMINED_VALUE,
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
