<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilModels\Parser\ActionParser;

trait WaitForActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function waitForActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait-for action, css selector' => [
                'fixture' => '/action-wait-for.html',
                'statement' => $actionParser->parse('wait-for $"#hello"', 0),
            ],
            'wait-for action, xpath expression' => [
                'fixture' => '/action-wait-for.html',
                'statement' => $actionParser->parse('wait-for $"//*[@id=\'hello\']"', 0),
            ],
        ];
    }
}
