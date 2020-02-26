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

trait CreateFromNotExistsAssertionDataProviderTrait
{
    public function createFromNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" not-exists' => [
                        'assertion' => $assertionParser->parse('$".selector" not-exists'),
                        'message' => '$".selector" not-exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" not-exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'assertionFailureMessageFactoryCalls' => [
                    '$".selector" exists' => [
                        'assertion' => $assertionParser->parse('$".selector" exists'),
                        'message' => '$".selector" exists failure message',
                    ],
                    '$".selector".attribute_name not-exists' => [
                        'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                        'message' => '$".selector".attribute_name not-exists failure message',
                    ],
                ],
                'expectedRenderedSource' =>
                    '{{ PHPUNIT }}->examinedValue = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector" exists failure message\'' . "\n" .
                    ');' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->examinedValue = {{ PHPUNIT }}->examinedValue !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ PHPUNIT }}->examinedValue,' . "\n" .
                    '    \'$".selector".attribute_name not-exists failure message\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                    Metadata::KEY_VARIABLE_EXPORTS => VariablePlaceholderCollection::createExportCollection([
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
