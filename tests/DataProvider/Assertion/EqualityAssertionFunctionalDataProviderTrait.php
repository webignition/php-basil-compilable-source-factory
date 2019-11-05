<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

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
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'page object examined value, scalar expected value' => [
                'fixture' => '/index.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
                'assertion' => null,
                'variableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
