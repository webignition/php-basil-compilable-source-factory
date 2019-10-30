<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Statement;
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
                'additionalSetupStatements' => new LineList([
                    new Statement(
                        '$this->assertEquals("Test fixture web server default document", self::$client->getTitle())'
                    ),
                    new Statement('$crawler = $crawler->filter(\'#link-to-assertions\')->getElement(0)->click()'),
                    new Statement('$this->assertEquals("Assertions fixture", self::$client->getTitle())'),
                ]),
                'teardownStatements' => new LineList([
                    new Statement(
                        '$this->assertEquals("Test fixture web server default document", self::$client->getTitle())'
                    )
                ]),
                'additionalVariableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => '$crawler',
                ],
            ],
        ];
    }
}
