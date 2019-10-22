<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
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
                'additionalSetupStatements' => [
                    '$this->assertEquals("Test fixture web server default document", self::$client->getTitle());',
                    '$crawler = $crawler->filter(\'#link-to-assertions\')->getElement(0)->click();',
                    '$this->assertEquals("Assertions fixture", self::$client->getTitle());',
                    'self::$client->back();',
                ],
                'teardownStatements' => [
                    '$this->assertEquals("Assertions fixture", self::$client->getTitle());',
                ],
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
