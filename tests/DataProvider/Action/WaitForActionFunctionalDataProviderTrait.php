<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilActionGenerator\ActionGenerator;

trait WaitForActionFunctionalDataProviderTrait
{
    public function waitForActionFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'wait-for action, css selector' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionGenerator->generate('wait-for "#hello"'),
            ],
            'wait-for action, xpath expression' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionGenerator->generate('wait-for "//*[@id=\'hello\']"'),
            ],
        ];
    }
}
