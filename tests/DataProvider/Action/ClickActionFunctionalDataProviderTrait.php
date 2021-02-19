<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait ClickActionFunctionalDataProviderTrait
{
    /**
     * @return array[]
     */
    public function clickActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $submitButtonPlaceholder = new VariableName('submitButton');

        return [
            'interaction action (click), link' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionParser->parse('click $"#link-to-index"'),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Click'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionParser->parse('click $"#form input[type=\'submit\']"'),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Click'),
                    StatementFactory::createCrawlerFilterCallForElement(
                        '#form input[type="submit"]',
                        $submitButtonPlaceholder
                    ),
                    StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
                'teardownStatements' => new Body([
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
