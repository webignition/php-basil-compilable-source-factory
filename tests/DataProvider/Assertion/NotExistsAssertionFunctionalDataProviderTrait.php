<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;

trait NotExistsAssertionFunctionalDataProviderTrait
{
    public function notExistsAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionParser->parse('$".selector".data-non-existent-attribute not-exists'),
                'variableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'not-exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$env.NON-EXISTENT not-exists'),
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
