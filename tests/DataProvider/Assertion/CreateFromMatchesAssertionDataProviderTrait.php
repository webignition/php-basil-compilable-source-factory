<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromMatchesAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromMatchesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'matches comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" matches "/^value/"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("/^value/" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertMatchesRegularExpression(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'matches comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name matches "/^value/"'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setExpectedValue("/^value/" ?? null);' . "\n"
                    . '{{ PHPUNIT }}->setExaminedValue((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})());' . "\n"
                    . '{{ PHPUNIT }}->assertMatchesRegularExpression(' . "\n"
                    . '    {{ PHPUNIT }}->getExpectedValue(),' . "\n"
                    . '    {{ PHPUNIT }}->getExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
