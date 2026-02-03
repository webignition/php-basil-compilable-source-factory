<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilModels\Parser\ActionParser;

trait WaitActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function waitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $fixture = '/action-wait.html';

        return [
            'wait action, literal duration' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait 10', 0)
                ),
            ],
            'wait action, element value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $"[id=\"element-value\"]"', 0),
                ),
            ],
            'wait action, attribute value, attribute exists' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $"[id=\"attribute-value\"]".data-duration', 0),
                ),
            ],
            'wait action, attribute value, attribute does not exist' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $"[id=\"attribute-value\"]".data-non-existent', 0),
                ),
            ],
            'wait action, browser property' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $browser.size', 0),
                ),
            ],
            'wait action, page property' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $page.title', 0),
                ),
            ],
            'wait action, environment value, value exists' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $env.DURATION', 0),
                ),
            ],
            'wait action, environment value, value does not exist' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait $env.NON_EXISTENT', 0),
                ),
            ],
        ];
    }
}
