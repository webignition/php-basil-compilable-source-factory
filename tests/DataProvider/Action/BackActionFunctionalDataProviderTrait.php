<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilModelFactory\Action\ActionFactory;

trait BackActionFunctionalDataProviderTrait
{
    public function backActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'back action' => [
                'fixture' => '/index.html',
                'action' => $actionFactory->createFromActionString('back'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                    StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ])
            ],
        ];
    }
}
