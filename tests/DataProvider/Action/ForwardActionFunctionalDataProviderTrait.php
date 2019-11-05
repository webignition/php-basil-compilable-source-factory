<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\PlaceholderFactory;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Statement;
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
                'additionalSetupStatements' => new LineList([
                    new Statement(sprintf(
                        '%s->assertEquals("Test fixture web server default document", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    )),
                    new Statement(sprintf(
                        '%s->filter(\'#link-to-assertions\')->getElement(0)->click()',
                        PlaceholderFactory::pantherCrawler()
                    )),
                    new Statement(sprintf(
                        '%s->assertEquals("Assertions fixture", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    )),
                    new Statement(sprintf(
                        '%s->back()',
                        PlaceholderFactory::pantherClient()
                    )),
                ]),
                'teardownStatements' => new LineList([
                    new Statement(sprintf(
                        '%s->assertEquals("Assertions fixture", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    )),
                ]),
            ],
        ];
    }
}
