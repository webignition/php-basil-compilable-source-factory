<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilModels\Parser\ActionParser;

trait WaitForActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function waitForActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $fixture = '/action-wait-for.html';

        return [
            'wait-for action, css selector' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait-for $"#hello"', 0),
                ),
            ],
            'wait-for action, xpath expression' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('wait-for $"//*[@id=\'hello\']"', 0),
                ),
            ],
        ];
    }
}
