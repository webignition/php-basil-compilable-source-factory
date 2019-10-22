<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;

trait WaitActionFunctionalDataProviderTrait
{
    public function waitActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        $fixture = '/action-wait.html';

        $emptyMetadata = new Metadata();

        return [
            'wait action, literal duration' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait 10'),
                'additionalSetupStatements' => [
                    '$this->assertTrue(true);',
                ],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'metadata' => $emptyMetadata,
                'expectedDuration' => 10,
            ],
            'wait action, element value' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('[id="element-value"]'))
                ),
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                    '$navigator = Navigator::create($crawler);',
                ],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withAdditionalClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])),
                'expectedDuration' => 20,
            ],
            'wait action, attribute value, attribute exists' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('[id="attribute-value"]'))->withAttributeName('data-duration')
                    )
                ),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withAdditionalClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])),
                'expectedDuration' => 30,
            ],
            'wait action, attribute value, attribute does not exist' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('[id="attribute-value"]'))->withAttributeName('data-non-existent')
                    )
                ),
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                    '$navigator = Navigator::create($crawler);',
                ],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withAdditionalClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])),
                'expectedDuration' => 0,
            ],
            'wait action, browser property' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $browser.size'),
                'additionalSetupStatements' => [],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                ],
                'metadata' => $emptyMetadata,
                'expectedDuration' => 1200,
            ],
            'wait action, page property' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $page.title'),
                'additionalSetupStatements' => [],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'metadata' => $emptyMetadata,
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value exists' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
                'additionalSetupStatements' => [],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                ],
                'metadata' => $emptyMetadata,
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value does not exist' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $env.NON_EXISTENT'),
                'additionalSetupStatements' => [],
                'teardownStatements' => [],
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                ],
                'metadata' => $emptyMetadata,
                'expectedDuration' => 0,
            ],
        ];
    }
}
