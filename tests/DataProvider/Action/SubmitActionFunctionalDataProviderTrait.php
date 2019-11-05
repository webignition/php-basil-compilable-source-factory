<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilModelFactory\Action\ActionFactory;

trait SubmitActionFunctionalDataProviderTrait
{
    public function submitActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        $fixture = '/action-click-submit.html';

        $setupStatements = new LineList([
            StatementFactory::createAssertBrowserTitle('Click'),
            StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
            StatementFactory::createCrawlerFilterCallForElement('#form', '$form'),
            StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-submitted\')'),
            StatementFactory::createAssertSame('"false"', '$form->getAttribute(\'data-submitted\')'),
        ]);

        $teardownStatements = new LineList([
            StatementFactory::createCrawlerFilterCallForElement('#form input[type="submit"]', '$submitButton'),
            StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-submitted\')'),
            StatementFactory::createCrawlerFilterCallForElement('#form', '$form'),
            StatementFactory::createAssertSame('"true"', '$form->getAttribute(\'data-submitted\')'),

        ]);

        $variableIdentifiers = [
            'HAS' => self::HAS_VARIABLE_NAME,
            'ELEMENT' => self::ELEMENT_VARIABLE_NAME,
        ];

        return [
            'interaction action (submit), form submit button' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('submit "#form input[type=\'submit\']"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
                'additionalVariableIdentifiers' => $variableIdentifiers,
            ],
            'interaction action (submit), form' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('submit "#form"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
                'additionalVariableIdentifiers' => $variableIdentifiers,
            ],
        ];
    }
}
