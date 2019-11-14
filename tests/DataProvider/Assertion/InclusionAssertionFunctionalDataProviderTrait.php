<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;

trait InclusionAssertionFunctionalDataProviderTrait
{
    public function inclusionAssertionFunctionalDataProvider(): array
    {
        return [
            'element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'page object examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => ResolvedVariableNames::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
