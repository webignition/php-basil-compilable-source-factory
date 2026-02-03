<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilModels\Model\Statement\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\AssertionParser;

trait IsRegExpAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function isRegExpAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $fixture = '/assertions.html';

        return [
            'is-regexp matches comparison, element identifier examined value, scalar expected value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" matches "/^\.selector [a-z]+$/"', 0),
                        '"/^\.selector [a-z]+$/"',
                        'is-regexp'
                    ),
                ),
            ],
            'is-regexp matches comparison, element identifier examined value, element identifier expected value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".matches-examined" matches $".matches-expected"', 0),
                        '$".matches-expected"',
                        'is-regexp'
                    ),
                ),
            ],
            'is-regexp matches comparison, element identifier examined value, attribute identifier expected value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" matches $".selector".data-matches-content', 0),
                        '$".selector".data-matches-content',
                        'is-regexp'
                    ),
                ),
            ],
        ];
    }
}
