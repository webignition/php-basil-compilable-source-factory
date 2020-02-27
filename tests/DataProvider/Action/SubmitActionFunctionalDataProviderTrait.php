<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait SubmitActionFunctionalDataProviderTrait
{
    public function submitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $fixture = '/action-click-submit.html';

        $submitPlaceholder = VariablePlaceholder::createExport('SUBMIT');
        $formPlaceholder = VariablePlaceholder::createExport('FORM');

        $setupStatements = new CodeBlock([
            StatementFactory::createAssertBrowserTitle('Click'),
//            StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', $submitPlaceholder),
//            StatementFactory::createCrawlerFilterCallForElement('#form', $formPlaceholder),
//            StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-submitted\')'),
//            StatementFactory::createAssertSame('"false"', '$form->getAttribute(\'data-submitted\')'),
        ]);

        $teardownStatements = new CodeBlock([
            StatementFactory::createAssertBrowserTitle('Form'),
//            StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', $submitPlaceholder),
//            StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-submitted\')'),
//            StatementFactory::createCrawlerFilterCallForElement('#form', $formPlaceholder),
//            StatementFactory::createAssertSame('"true"', '$form->getAttribute(\'data-submitted\')'),
        ]);

        $variableIdentifiers = [
            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
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
