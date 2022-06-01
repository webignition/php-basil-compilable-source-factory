<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait SubmitActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function submitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $fixture = '/action-click-submit.html';

        $setupStatements = new Body([
            StatementFactory::createAssertBrowserTitle('Click'),
        ]);

        $teardownStatements = new Body([
            StatementFactory::createAssertBrowserTitle('Form'),
        ]);

        return [
            'interaction action (submit), form submit button' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('submit $"#form input[type=\'submit\']"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
            ],
            'interaction action (submit), form' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('submit $"#form"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
            ],
        ];
    }
}
