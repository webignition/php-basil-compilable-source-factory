<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilModels\Parser\ActionParser;

trait WaitForActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function waitForActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait-for action, css selector' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionParser->parse('wait-for $"#hello"'),
            ],
            'wait-for action, xpath expression' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionParser->parse('wait-for $"//*[@id=\'hello\']"'),
            ],
        ];
    }
}
