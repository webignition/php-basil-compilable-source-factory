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
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromWaitActionDataProviderTrait
{
    public function createFromWaitActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait action, literal' => [
                'action' => $actionFactory->createFromActionString('wait 30'),
                'expectedContent' => new LineList([
                    new Statement('{{ DURATION }} = "30" ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, element value' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('.duration-selector'))
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.duration-selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ DURATION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.duration-selector\'))'),
                    new Statement('{{ DURATION }} = '
                        . '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ DURATION }}) ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'DURATION',
                    ])),
            ],
            'wait action, attribute value' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('.duration-selector'))->withAttributeName('attribute_name')
                    )
                ),
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.duration-selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement(
                        '{{ DURATION }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.duration-selector\'))'
                    ),
                    new Statement('{{ DURATION }} = {{ DURATION }}->getAttribute(\'attribute_name\') ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'DURATION',
                    ])),
            ],
            'wait action, browser property' => [
                'action' => $actionFactory->createFromActionString('wait $browser.size'),
                'expectedContent' => new LineList([
                    new Statement(
                        '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()'
                    ),
                    new Statement('{{ DURATION }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                        'DURATION',
                    ])),
            ],
            'wait action, page property' => [
                'action' => $actionFactory->createFromActionString('wait $page.title'),
                'expectedContent' => new LineList([
                    new Statement('{{ DURATION }} = {{ CLIENT }}->getTitle() ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
            'wait action, environment value' => [
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
                'expectedContent' => new LineList([
                    new Statement('{{ DURATION }} = {{ ENV }}[\'DURATION\'] ?? 0'),
                    new Statement('{{ DURATION }} = (int) {{ DURATION }}'),
                    new Statement('usleep({{ DURATION }} * 1000)'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'DURATION',
                    ])),
            ],
        ];
    }
}
