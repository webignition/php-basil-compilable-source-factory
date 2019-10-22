<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModelFactory\Action\ActionFactory;

trait ReloadActionFunctionalDataProviderTrait
{
    public function reloadActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'reload action' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionFactory->createFromActionString('reload'),
                'additionalSetupStatements' => [
                    '$this->assertCount(0, $crawler->filter("#hello"));',
                    'usleep(100000);',
                    '$this->assertCount(1, $crawler->filter("#hello"));',
                ],
                'teardownStatements' => [
                    '$this->assertCount(0, $crawler->filter("#hello"));',
                    'usleep(100000);',
                    '$this->assertCount(1, $crawler->filter("#hello"));',
                ],
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
