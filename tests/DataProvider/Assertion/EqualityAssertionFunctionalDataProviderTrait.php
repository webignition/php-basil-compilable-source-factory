<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;

trait EqualityAssertionFunctionalDataProviderTrait
{
    public function equalityAssertionFunctionalDataProvider(): array
    {
        return [
            'element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                ],
            ],
            'page object examined value, scalar expected value' => [
                'fixture' => '/index.html',
                'assertion' => null,
            ],
            'element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                    'ELEMENT' => '$element',
                ],
            ],
            'attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    'ELEMENT' => '$element',
                ],
            ],
            'attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'ELEMENT' => '$element',
                ],
            ],
        ];
    }
}
