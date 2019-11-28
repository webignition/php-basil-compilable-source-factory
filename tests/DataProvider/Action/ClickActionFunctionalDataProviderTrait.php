<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilParser\ActionParser;

trait ClickActionFunctionalDataProviderTrait
{
    public function clickActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action (click), link' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionParser->parse('click $"#link-to-index"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Click'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ]
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionParser->parse('click $"#form input[type=\'submit\']"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Click'),
                    StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
                    StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
                    StatementFactory::createAssertSame('"true"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
