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
                'expectedRenderedSource' =>
                    '{{ EXAMINED }} = {{ NAVIGATOR }}->has(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\" not-exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\"",' . "\n" .
                    '        "comparison": "not-exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
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
                        VariableNames::EXAMINED_VALUE,
                    ]),
                ]),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'expectedRenderedSource' =>
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '    "locator": ".selector"' . "\n" .
                    '}\'));' . "\n" .
                    '{{ PHPUNIT }}->assertTrue(' . "\n" .
                    '    {{ HAS }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\".attribute_name not-exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\".attribute_name",' . "\n" .
                    '        "comparison": "not-exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
                    ');' . "\n" .
                    '{{ EXAMINED }} = (function () {' . "\n" .
                    '    {{ ELEMENT }} = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    "\n" .
                    '    return {{ ELEMENT }}->getAttribute(\'attribute_name\');' . "\n" .
                    '})();' . "\n" .
                    '{{ EXAMINED }} = {{ EXAMINED }} !== null;' . "\n" .
                    '{{ PHPUNIT }}->assertFalse(' . "\n" .
                    '    {{ EXAMINED }},' . "\n" .
                    '    \'{' . "\n" .
                    '    "assertion": {' . "\n" .
                    '        "source": "$\\\".selector\\\".attribute_name not-exists",' . "\n" .
                    '        "identifier": "$\\\".selector\\\".attribute_name",' . "\n" .
                    '        "comparison": "not-exists"' . "\n" .
                    '    }' . "\n" .
                    '}\'' . "\n" .
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
                        VariableNames::EXAMINED_VALUE,
                        'HAS',
                        'ELEMENT',
                    ]),
                ]),
            ],
        ];
    }
}
