<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilModels\Parser\AssertionParser;

trait MatchesAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function matchesAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'matches comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$".selector" matches "/^\.selector [a-z]+$/"', 0),
            ],
            'matches comparison, attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse(
                    '$".selector".data-test-attribute matches "/^[a-z]+ content$/"',
                    0,
                ),
            ],
            'matches comparison, environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$env.TEST1 matches "/^environment/"', 0),
                'additionalVariableIdentifiers' => [
                    DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
            'matches comparison, browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
                'statement' => $assertionParser->parse('$browser.size matches "/[0-9]+x[0-9]+/"', 0),
            ],
            'matches comparison, page object examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$page.title matches "/fixture$/"', 0),
            ],
            'matches comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$".matches-examined" matches $".matches-expected"', 0),
            ],
            'matches comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$".selector" matches $".selector".data-matches-content', 0),
            ],
            'matches comparison, attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
                'statement' => $assertionParser->parse('$".selector".data-environment-value matches $env.MATCHES', 0),
                'additionalVariableIdentifiers' => [
                    DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
