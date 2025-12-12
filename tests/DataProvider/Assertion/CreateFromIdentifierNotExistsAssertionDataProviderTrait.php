<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierNotExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $expectedMetadata = new Metadata(
            classNames: [
                ElementIdentifier::class,
                InvalidLocatorException::class,
            ],
            variableNames: [
                VariableName::PHPUNIT_TEST_CASE,
                VariableName::DOM_CRAWLER_NAVIGATOR,
            ],
        );

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->examinedElementIdentifier = '
                    . 'ElementIdentifier::fromJson(\'{' . "\n"
                    . '    "locator": ".selector"' . "\n"
                    . '}\');' . "\n"
                    . 'try {' . "\n"
                    . '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n"
                    . '        {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)' . "\n"
                    . '    );' . "\n"
                    . '} catch (InvalidLocatorException $exception) {' . "\n"
                    . '    self::staticSetLastException($exception);' . "\n"
                    . '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n"
                    . '}' . "\n"
                    . '{{ PHPUNIT }}->assertFalse(' . "\n"
                    . '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => $expectedMetadata,
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->examinedElementIdentifier = '
                    . 'ElementIdentifier::fromJson(\'{' . "\n"
                    . '    "locator": ".selector"' . "\n"
                    . '}\');' . "\n"
                    . 'try {' . "\n"
                    . '    {{ PHPUNIT }}->setBooleanExaminedValue(' . "\n"
                    . '        {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)' . "\n"
                    . '    );' . "\n"
                    . '} catch (InvalidLocatorException $exception) {' . "\n"
                    . '    self::staticSetLastException($exception);' . "\n"
                    . '    {{ PHPUNIT }}->fail(\'Invalid locator\');' . "\n"
                    . '}' . "\n"
                    . '{{ PHPUNIT }}->assertTrue(' . "\n"
                    . '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n"
                    . ');' . "\n"
                    . '{{ PHPUNIT }}->setBooleanExaminedValue(((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})() ?? null) !== null);' . "\n"
                    . '{{ PHPUNIT }}->assertFalse(' . "\n"
                    . '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
