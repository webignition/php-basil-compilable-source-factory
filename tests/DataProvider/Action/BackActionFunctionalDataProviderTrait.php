<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilParser\ActionParser;

trait BackActionFunctionalDataProviderTrait
{
    public function backActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
//            'back action' => [
//                'fixture' => '/index.html',
//                'action' => $actionParser->parse('back'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
//                    StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
//                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
//                ])
//            ],
        ];
    }
}
