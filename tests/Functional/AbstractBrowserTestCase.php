<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\Panther\Client;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
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

    protected function createExecutableCallForRequest(
        string $fixture,
        SourceInterface $lineList,
        array $additionalSetupStatements = [],
        array $teardownStatements = [],
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
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

        $metadata = $metadata ?? new Metadata();

        return $this->executableCallFactory->create(
            $lineList,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $metadata
        );
    }

    protected function createExecutableCallForRequestWithReturn(
        string $fixture,
        SourceInterface $source,
        array $additionalSetupStatements = [],
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
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

        $metadata = $metadata ?? new Metadata();

        return $this->executableCallFactory->createWithReturn(
            $source,
            $variableIdentifiers,
            $setupStatements,
            [],
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
