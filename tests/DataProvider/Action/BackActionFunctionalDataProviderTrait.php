<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilActionGenerator\ActionGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;

trait BackActionFunctionalDataProviderTrait
{
    public function backActionFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'back action' => [
                'fixture' => '/index.html',
                'action' => $actionGenerator->generate('back'),
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
