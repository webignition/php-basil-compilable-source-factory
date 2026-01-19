<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilModels\Parser\AssertionParser;

trait NotExistsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function notExistsAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$".selector" not-exists', 0),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$".selector".data-non-existent-attribute not-exists', 0),
            ],
            'not-exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$env.NON-EXISTENT not-exists', 0),
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
