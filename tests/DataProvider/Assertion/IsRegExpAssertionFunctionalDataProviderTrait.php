<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilParser\AssertionParser;

trait IsRegExpAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function isRegExpAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'matches comparison, element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^\.selector [a-z]+$/"'),
                    '"/^\.selector [a-z]+$/"',
                    'is-regexp'
                ),
            ],
            'matches comparison, element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".matches-examined" matches $".matches-expected"'),
                    '$".matches-expected"',
                    'is-regexp'
                ),
            ],
            'matches comparison, element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".selector".data-matches-content'),
                    '$".selector".data-matches-content',
                    'is-regexp'
                ),
            ],
        ];
    }
}
