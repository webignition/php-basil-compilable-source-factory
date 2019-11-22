<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilActionGenerator\ActionGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\PlaceholderFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;

trait ForwardActionFunctionalDataProviderTrait
{
    public function forwardActionFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'forward action' => [
                'fixture' => '/index.html',
                'action' => $actionGenerator->generate('forward'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                    StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
                    StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                    StatementFactory::createClientAction('back')
                ]),
                'teardownStatements' => CodeBlock::fromContent([
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
