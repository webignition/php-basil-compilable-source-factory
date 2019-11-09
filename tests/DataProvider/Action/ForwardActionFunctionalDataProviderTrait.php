<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\PlaceholderFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilModelFactory\Action\ActionFactory;

trait ForwardActionFunctionalDataProviderTrait
{
    public function forwardActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'forward action' => [
                'fixture' => '/index.html',
                'action' => $actionFactory->createFromActionString('forward'),
                'additionalSetupStatements' => new Block([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                    StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                    StatementFactory::createClientAction('back')
                ]),
                'teardownStatements' => Block::fromContent([
                    sprintf(
                        '%s->assertEquals("Assertions fixture", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    ),
                ]),
            ],
        ];
    }
}
