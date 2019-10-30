<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
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

        return [
            'wait action, literal duration' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait 10'),
                'additionalSetupStatements' => new LineList([
                    new Statement('$this->assertTrue(true)')
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'metadata' => null,
                'expectedDuration' => 10,
            ],
            'wait action, element value' => [
                'fixture' => $fixture,
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('[id="element-value"]'))
                ),
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
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
                'additionalSetupStatements' => new LineList([
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
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
                'additionalSetupStatements' => new LineList([
                    new Statement('$inspector = Inspector::create()'),
                    new Statement('$navigator = Navigator::create($crawler)'),
                ]),
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                        new ClassDependency(Navigator::class),
                    ])),
                'expectedDuration' => 0,
            ],
            'wait action, browser property' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $browser.size'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                ],
                'metadata' => null,
                'expectedDuration' => 1200,
            ],
            'wait action, page property' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $page.title'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                ],
                'metadata' => null,
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value exists' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                ],
                'metadata' => null,
                'expectedDuration' => 5,
            ],
            'wait action, environment value, value does not exist' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('wait $env.NON_EXISTENT'),
                'additionalSetupStatements' => null,
                'teardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'DURATION' => '$duration',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                ],
                'metadata' => null,
                'expectedDuration' => 0,
            ],
        ];
    }
}
