<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromExcludesAssertionDataProviderTrait
{
    public function createFromExcludesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "value"'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n" .
                    '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ INSPECTOR }}->getValue($element);' . "\n" .
                    '})());' . "\n" .
                    '{{ PHPUNIT }}->assertStringNotContainsString(' . "\n" .
                    '    (string) ({{ PHPUNIT }}->getExpectedValue()),' . "\n" .
                    '    (string) ({{ PHPUNIT }}->getExaminedValue())' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name excludes "value"'),
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n" .
                    '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return $element->getAttribute(\'attribute_name\');' . "\n" .
                    '})());' . "\n" .
                    '{{ PHPUNIT }}->assertStringNotContainsString(' . "\n" .
                    '    (string) ({{ PHPUNIT }}->getExpectedValue()),' . "\n" .
                    '    (string) ({{ PHPUNIT }}->getExaminedValue())' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
