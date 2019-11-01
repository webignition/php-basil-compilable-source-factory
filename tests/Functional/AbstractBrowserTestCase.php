<?php

namespace webignition\BasilCompilableSourceFactory\Tests\Functional;

use Facebook\WebDriver\WebDriverDimension;
use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\BasePantherTestCase\AbstractBrowserTestCase as BaseAbstractBrowserTestCase;

abstract class AbstractBrowserTestCase extends BaseAbstractBrowserTestCase
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
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    public static function setUpBeforeClass(): void
    {
        self::$webServerDir = __DIR__
            . '/..'
            . self::FIXTURES_RELATIVE_PATH
            . self::FIXTURES_HTML_RELATIVE_PATH;

        parent::setUpBeforeClass();
        self::$client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1200, 1100));
    }

    protected function setUp(): void
    {
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    protected function createExecutableCallForRequest(
        string $fixture,
        SourceInterface $source,
        ?LineList $additionalSetupStatements = null,
        ?LineList $teardownStatements = null,
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
    ) {
        $requestUrl = Options::getBaseUri() . $fixture;

        $setupStatements = new LineList([
            new Statement('$crawler = self::$client->request(\'GET\', \'' . $requestUrl . '\')'),
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
        $requestUrl = Options::getBaseUri() . $fixture;

        $setupStatements = new LineList([
            new Statement('$crawler = self::$client->request(\'GET\', \'' . $requestUrl . '\')'),
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
