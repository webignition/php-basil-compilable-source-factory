<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\SourceInterface;

abstract class AbstractBrowserTestCase extends AbstractTestCase
{
    const FIXTURES_RELATIVE_PATH = '/Fixtures';
    const FIXTURES_HTML_RELATIVE_PATH = '/html';

    const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';

    /**
     * @var Client
     */
    protected static $client;

    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = (string) realpath(
            __DIR__  . '/..' . self::FIXTURES_RELATIVE_PATH . self::FIXTURES_HTML_RELATIVE_PATH
        );

        self::$client = self::createPantherClient();
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));
    }

    protected function createExecutableCallForRequestWithReturn(
        string $fixture,
        SourceInterface $source,
        array $additionalSetupStatements = [],
        array $additionalVariableIdentifiers = []
    ) {
        $setupStatements = array_merge(
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
            ],
            $additionalSetupStatements
        );

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        return $this->executableCallFactory->createWithReturn(
            $source,
            $variableIdentifiers,
            $setupStatements
        );
    }
}
