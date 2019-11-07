<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModelFactory\Action\ActionFactory;

trait ClickActionFunctionalDataProviderTrait
{
    public function clickActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (click), link' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionFactory->createFromActionString('click "#link-to-index"'),
                'additionalSetupStatements' => new LineList([
                    StatementFactory::createAssertBrowserTitle('Click'),
                ]),
                'teardownStatements' => new LineList([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => ResolvedVariableNames::ELEMENT_VARIABLE_NAME,
                    'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
                ]
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionFactory->createFromActionString('click "#form input[type=\'submit\']"'),
                'additionalSetupStatements' => new LineList([
                    StatementFactory::createAssertBrowserTitle('Click'),
                    StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
                    StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
                'teardownStatements' => new LineList([
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
