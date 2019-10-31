<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\ProcessManager\WebServerManager;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

abstract class AbstractBrowserTestCase extends AbstractTestCase
{
    const FIXTURES_RELATIVE_PATH = '/Fixtures';
    const FIXTURES_HTML_RELATIVE_PATH = '/html';

    const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$navigator';
    const WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME = '$inspector';
    const WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME = '$mutator';
    const EXAMINED_VALUE_VARIABLE_NAME = '$examinedValue';
    const EXPECTED_VALUE_VARIABLE_NAME = '$expectedValue';
    const HAS_VARIABLE_NAME = '$has';
    const ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME = '$_ENV';
    const WEBDRIVER_DIMENSION_VARIABLE_NAME = '$webDriverDimension';
    const PANTHER_CRAWLER_VARIABLE_NAME = '$crawler';
    const ELEMENT_VARIABLE_NAME = '$element';
    const COLLECTION_VARIABLE_NAME = '$collection';
    const VALUE_VARIABLE_NAME = '$value';

    /**
     * @var PantherClient|null
     */
    protected static $client;

    /**
     * @var string|null
     */
    protected static $webServerDir;

    /**
     * @var array
     */
    protected static $defaultOptions = [
        'hostname' => '127.0.0.1',
        'port' => 9080,
    ];

    /**
     * @var WebServerManager|null
     */
    protected static $webServerManager;

    /**
     * @var string|null
     */
    protected static $baseUri;

    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = (string) realpath(
            __DIR__  . '/..' . self::FIXTURES_RELATIVE_PATH . self::FIXTURES_HTML_RELATIVE_PATH
        );

        self::startWebServer();
        self::$client = PantherClient::createChromeClient(null, null, [], self::$baseUri);
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));
    }

    public static function tearDownAfterClass(): void
    {
        static::stopWebServer();
    }

    public static function startWebServer(): void
    {
        if (null !== static::$webServerManager) {
            return;
        }

        self::$webServerManager = new WebServerManager(
            (string) static::$webServerDir,
            self::$defaultOptions['hostname'],
            self::$defaultOptions['port']
        );
        self::$webServerManager->start();

        self::$baseUri = sprintf('http://%s:%s', self::$defaultOptions['hostname'], self::$defaultOptions['port']);
    }


    public static function stopWebServer()
    {
        if (null !== self::$webServerManager) {
            self::$webServerManager->quit();
            self::$webServerManager = null;
        }

        if (null !== self::$client) {
            self::$client->quit(false);
            self::$client->getBrowserManager()->quit();
            self::$client = null;
        }
    }

    protected function createExecutableCallForRequest(
        string $fixture,
        SourceInterface $source,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
    ) {
        $setupStatements = new LineList([
            new Statement('$crawler = self::$client->request(\'GET\', \'' . $fixture . '\')'),
            $additionalSetupStatements,
        ]);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $metadata = $metadata ?? new Metadata();

        return $this->executableCallFactory->create(
            $source,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $metadata
        );
    }

    protected function createExecutableCallForRequestWithReturn(
        string $fixture,
        SourceInterface $source,
        ?LineList $additionalSetupStatements = null,
        ?LineList $additionalTeardownStatements = null,
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
    ) {
        $setupStatements = new LineList([
            new Statement('$crawler = self::$client->request(\'GET\', \'' . $fixture . '\')'),
            $additionalSetupStatements,
        ]);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $metadata = $metadata ?? new Metadata();

        return $this->executableCallFactory->createWithReturn(
            $source,
            $variableIdentifiers,
            $setupStatements,
            $additionalTeardownStatements,
            $metadata
        );
    }

    protected function addNavigatorToMetadata(MetadataInterface $metadata): MetadataInterface
    {
        $metadata->addClassDependencies(new ClassDependencyCollection([
            new ClassDependency(Navigator::class),
        ]));

        return $metadata;
    }
}
