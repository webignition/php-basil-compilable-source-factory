<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
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
                'statement' => $assertionParser->parse('$".selector" exists', 0),
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$".selector".data-test-attribute exists', 0),
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$env.TEST1 exists', 0),
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$browser.size exists', 0),
            ],
            'exists comparison, page object value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$page.title exists', 0),
            ],
            'exists comparison, element identifier examined value, selector contains single quotes (1)' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse(
                    '$"[data-value=\"\'data attribute within single quotes\'\"]" exists',
                    0,
                ),
            ],
            'exists comparison, element identifier examined value, selector contains single quotes (2)' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse(
                    '$"[data-value=\"data attribute \'containing\' single quotes\"]" exists',
                    0,
                ),
            ],
        ];
    }
}
