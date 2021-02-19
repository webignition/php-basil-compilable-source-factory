<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\AssertionParser;

trait NotExistsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array[]
     */
    public function notExistsAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionParser->parse('$".selector".data-non-existent-attribute not-exists'),
            ],
            'not-exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionParser->parse('$env.NON-EXISTENT not-exists'),
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
