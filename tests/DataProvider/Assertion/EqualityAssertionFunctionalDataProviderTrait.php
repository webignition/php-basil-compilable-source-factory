<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;

trait EqualityAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<
     *     string,
     *     array{"fixture": string, "statement": null, "additionalVariableIdentifiers"?: array<string, string>}
     * >
     */
    public static function equalityAssertionFunctionalDataProvider(): array
    {
        return [
            'element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
            ],
            'attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
            ],
            'environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'statement' => null,
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'statement' => null,
            ],
            'page object examined value, scalar expected value' => [
                'fixture' => '/index.html',
                'statement' => null,
            ],
            'element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
            ],
            'attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
                'additionalVariableIdentifiers' => [
                    VariableName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
            ],
            'attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
                'statement' => null,
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'statement' => null,
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'statement' => null,
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'statement' => null,
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'statement' => null,
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'statement' => null,
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'statement' => null,
            ],
        ];
    }
}
