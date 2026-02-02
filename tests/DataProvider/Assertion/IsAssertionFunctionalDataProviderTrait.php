<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilModels\Parser\AssertionParser;

trait IsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function isAssertionFunctionalDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $defineStatementVariableBody = new Body(
            BodyContentCollection::createFromExpressions([
                new AssignmentExpression(
                    Property::asStringVariable('statement_0'),
                    LiteralExpression::string('"{}"')
                ),
            ]),
        );

        $assertions = [
            'element identifier examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$".selector" is ".selector content"', 0),
            ],
            'attribute identifier examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-test-attribute is "attribute content"', 0),
            ],
            'environment examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$env.TEST1 is "environment value"', 0),
            ],
            'browser object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$browser.size is "1200x1100"', 0),
            ],
            'page object examined value, scalar expected value' => [
                'statement' => $assertionParser->parse('$page.title is "Test fixture web server default document"', 0),
            ],
            'element identifier examined value, element identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" is $".selector"', 0),
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'statement' => $assertionParser->parse('$".selector" is $".selector".data-is-selector-content', 0),
            ],
            'attribute identifier examined value, environment expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-environment-value is $env.TEST1', 0),
            ],
            'attribute identifier examined value, browser object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-browser-size is $browser.size', 0),
            ],
            'attribute identifier examined value, page object expected value' => [
                'statement' => $assertionParser->parse('$".selector".data-page-title is $page.title', 0),
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'statement' => $assertionParser->parse('$".select-none-selected" is "none-selected-1"', 0),
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'statement' => $assertionParser->parse('$".select-has-selected" is "has-selected-2"', 0),
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'statement' => $assertionParser->parse('$".select-none-selected option" is "none-selected-1"', 0),
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'statement' => $assertionParser->parse('$".select-has-selected option" is "has-selected-2"', 0),
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'statement' => $assertionParser->parse('$"input[name=radio-not-checked]" is ""', 0),
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'statement' => $assertionParser->parse('$"input[name=radio-checked]" is "checked-2"', 0),
            ],
        ];

        $testCases = [];

        foreach (self::equalityAssertionFunctionalDataProvider() as $testName => $testData) {
            $testDataModel = new StatementHandlerTestData($testData['fixture'], $assertions[$testName]['statement']);
            $testDataModel = $testDataModel->withBeforeTest($defineStatementVariableBody);

            $testCases['is comparison, ' . $testName] = [
                'data' => $testDataModel,
            ];
        }

        return $testCases;
    }
}
