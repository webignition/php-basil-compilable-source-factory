<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModelFactory\AssertionFactory;

trait ExistsAssertionFunctionalDataProviderTrait
{
    public function existsAssertionFunctionalDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/assertions.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".data-test-attribute exists'
                ),
                'variableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 exists'
                ),
                'variableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size exists'
                ),
                'variableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
            'exists comparison, page object value' => [
                'fixture' => '/empty.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title exists'
                ),
                'variableIdentifiers' => [
                    VariableNames::EXAMINED_VALUE => ResolvedVariableNames::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
