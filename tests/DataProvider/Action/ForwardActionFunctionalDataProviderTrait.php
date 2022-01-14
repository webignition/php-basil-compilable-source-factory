<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilParser\ActionParser;

trait ForwardActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function forwardActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'forward action' => [
                'fixture' => '/index.html',
                'action' => $actionParser->parse('forward'),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                    StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                    StatementFactory::createClientAction('back')
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                ]),
            ],
        ];
    }
}
