<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
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
                    new Statement(
                        '$this->assertEquals("Test fixture web server default document", self::$client->getTitle())'
                    ),
                    new Statement('$crawler = $crawler->filter(\'#link-to-assertions\')->getElement(0)->click()'),
                    new Statement('$this->assertEquals("Assertions fixture", self::$client->getTitle())'),
                    new Statement('self::$client->back()'),
                ]),
                'teardownStatements' => new LineList([
                    new Statement('$this->assertEquals("Assertions fixture", self::$client->getTitle())'),
                ]),
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
