<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\ResolvingPlaceholder;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait ClickActionFunctionalDataProviderTrait
{
    public function clickActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $submitButtonPlaceholder = new ResolvingPlaceholder('submitButton');

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
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionParser->parse('click $"#form input[type=\'submit\']"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Click'),
                    StatementFactory::createCrawlerFilterCallForElement(
                        '#form input[type="submit"]',
                        $submitButtonPlaceholder
                    ),
                    StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
                'teardownStatements' => new CodeBlock([
                  StatementFactory::createCrawlerFilterCallForElement(
                      '#form input[type="submit"]',
                      $submitButtonPlaceholder
                  ),
                    StatementFactory::createAssertSame('"true"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
            ],
        ];
    }
}
