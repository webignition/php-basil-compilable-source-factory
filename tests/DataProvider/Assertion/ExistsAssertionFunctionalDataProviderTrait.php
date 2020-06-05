<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;

trait ExistsAssertionFunctionalDataProviderTrait
{
    public function existsAssertionFunctionalDataProvider(): array
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
                'variableIdentifiers' => [
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
        ];
    }
}
