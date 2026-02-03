<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilModels\Parser\ActionParser;

trait BackActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function backActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'back action' => [
                'data' => new StatementHandlerTestData(
                    '/index.html',
                    $actionParser->parse('back', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                        )
                        ->append(
                            StatementFactory::createCrawlerActionCallForElement('#link-to-assertions', 'click'),
                        )
                        ->append(
                            StatementFactory::createAssertBrowserTitle('Assertions fixture'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                        )
                )),
            ],
        ];
    }
}
