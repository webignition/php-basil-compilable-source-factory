<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait BackActionFunctionalDataProviderTrait
{
    public function backActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'back action' => [
                'fixture' => '/index.html',
                'action' => $actionParser->parse('back'),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                    StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
            ],
        ];
    }
}
