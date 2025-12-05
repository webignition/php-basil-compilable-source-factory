<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\AssertionParser;

trait ExistsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function existsAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionParser->parse('$".selector" exists'),
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionParser->parse('$".selector".data-test-attribute exists'),
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$env.TEST1 exists'),
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$browser.size exists'),
            ],
            'exists comparison, page object value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$page.title exists'),
            ],
            'exists comparison, element identifier examined value, selector contains single quotes (1)' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionParser->parse(
                    '$"[data-value=\"\'data attribute within single quotes\'\"]" exists'
                ),
            ],
            'exists comparison, element identifier examined value, selector contains single quotes (2)' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionParser->parse(
                    '$"[data-value=\"data attribute \'containing\' single quotes\"]" exists'
                ),
            ],
        ];
    }
}
