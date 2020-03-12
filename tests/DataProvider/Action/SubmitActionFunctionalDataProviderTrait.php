<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait SubmitActionFunctionalDataProviderTrait
{
    public function submitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $fixture = '/action-click-submit.html';

        $setupStatements = new CodeBlock([
            StatementFactory::createAssertBrowserTitle('Click'),
        ]);

        $teardownStatements = new CodeBlock([
            StatementFactory::createAssertBrowserTitle('Form'),
        ]);

        $variableIdentifiers = [
            'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
            'SUBMIT' => '$submitButton',
            'FORM' => '$form',
        ];

        return [
            'interaction action (submit), form submit button' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('submit $"#form input[type=\'submit\']"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
                'additionalVariableIdentifiers' => $variableIdentifiers,
            ],
            'interaction action (submit), form' => [
                'fixture' => $fixture,
                'action' => $actionParser->parse('submit $"#form"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
                'additionalVariableIdentifiers' => $variableIdentifiers,
            ],
        ];
    }
}
