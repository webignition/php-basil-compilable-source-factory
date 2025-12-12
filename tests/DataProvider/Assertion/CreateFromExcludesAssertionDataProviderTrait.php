<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromExcludesAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromExcludesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertStringNotContainsString(' . "\n"
                    . '    (string) ({{ PHPUNIT }}->getExpectedValue()),' . "\n"
                    . '    (string) ({{ PHPUNIT }}->getExaminedValue())' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name excludes "value"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("value" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertStringNotContainsString(' . "\n"
                    . '    (string) ({{ PHPUNIT }}->getExpectedValue()),' . "\n"
                    . '    (string) ({{ PHPUNIT }}->getExaminedValue())' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
