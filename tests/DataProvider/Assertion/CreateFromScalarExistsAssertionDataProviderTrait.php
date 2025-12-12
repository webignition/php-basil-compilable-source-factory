<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromScalarExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromScalarExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, page property examined value' => [
                'assertion' => $assertionParser->parse('$page.url exists'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->'
                    . 'setBooleanExaminedValue(({{ CLIENT }}->getCurrentURL() ?? null) !== null);' . "\n"
                    . '{{ PHPUNIT }}->assertTrue(' . "\n"
                    . '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'exists comparison, data parameter value' => [
                'assertion' => $assertionParser->parse('$data.key exists'),
                'expectedRenderedContent' => '{{ PHPUNIT }}->setBooleanExaminedValue(($key ?? null) !== null);' . "\n"
                    . '{{ PHPUNIT }}->assertTrue(' . "\n"
                    . '    {{ PHPUNIT }}->getBooleanExaminedValue()' . "\n"
                    . ');',
                'expectedMetadata' => Metadata::create(
                    variableNames: [
                        VariableNames::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
