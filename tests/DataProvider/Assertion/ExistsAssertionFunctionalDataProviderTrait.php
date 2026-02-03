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

trait ExistsAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function existsAssertionFunctionalDataProvider(): array
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

        return [
            'exists comparison, element identifier examined value' => [
                'data' => new StatementHandlerTestData(
                    '/assertions.html',
                    $assertionParser->parse('$".selector" exists', 0),
                )->withBeforeTest($defineStatementVariableBody),
            ],
            'exists comparison, attribute identifier examined value' => [
                'data' => new StatementHandlerTestData(
                    '/assertions.html',
                    $assertionParser->parse('$".selector".data-test-attribute exists', 0),
                )->withBeforeTest($defineStatementVariableBody),
            ],
            'exists comparison, environment examined value' => [
                'data' => new StatementHandlerTestData(
                    '/empty.html',
                    $assertionParser->parse('$env.TEST1 exists', 0),
                )->withBeforeTest($defineStatementVariableBody),
            ],
            'exists comparison, browser object value' => [
                'data' => new StatementHandlerTestData(
                    '/empty.html',
                    $assertionParser->parse('$browser.size exists', 0),
                )->withBeforeTest($defineStatementVariableBody),
            ],
            'exists comparison, page object value' => [
                'data' => new StatementHandlerTestData(
                    '/empty.html',
                    $assertionParser->parse('$page.title exists', 0),
                )->withBeforeTest($defineStatementVariableBody),
            ],
            'exists comparison, element identifier examined value, selector contains single quotes (1)' => [
                'data' => new StatementHandlerTestData(
                    '/assertions.html',
                    $assertionParser->parse(
                        '$"[data-value=\"\'data attribute within single quotes\'\"]" exists',
                        0,
                    ),
                )->withBeforeTest($defineStatementVariableBody),
            ],
            'exists comparison, element identifier examined value, selector contains single quotes (2)' => [
                'data' => new StatementHandlerTestData(
                    '/assertions.html',
                    $assertionParser->parse(
                        '$"[data-value=\"data attribute \'containing\' single quotes\"]" exists',
                        0,
                    ),
                )->withBeforeTest($defineStatementVariableBody),
            ],
        ];
    }
}
