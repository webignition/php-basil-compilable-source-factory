<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilModels\Parser\ActionParser;

trait ForwardActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function forwardActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'forward action' => [
                'fixture' => '/index.html',
                'action' => $actionParser->parse('forward', 0),
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
