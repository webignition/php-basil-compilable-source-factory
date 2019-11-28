<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilParser\ActionParser;

trait SubmitActionFunctionalDataProviderTrait
{
    public function submitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $fixture = '/action-click-submit.html';

        $setupStatements = new CodeBlock([
            StatementFactory::createAssertBrowserTitle('Click'),
            StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
            StatementFactory::createCrawlerFilterCallForElement('#form', '$form'),
            StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-submitted\')'),
            StatementFactory::createAssertSame('"false"', '$form->getAttribute(\'data-submitted\')'),
        ]);

        $teardownStatements = new CodeBlock([
            StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
            StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-submitted\')'),
            StatementFactory::createCrawlerFilterCallForElement('#form', '$form'),
            StatementFactory::createAssertSame('"true"', '$form->getAttribute(\'data-submitted\')'),

        ]);

        $variableIdentifiers = [
            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
            'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
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
