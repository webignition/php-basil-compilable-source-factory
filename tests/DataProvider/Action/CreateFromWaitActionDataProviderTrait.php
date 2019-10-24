<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
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
                'expectedStatements' => [
                    '{{ DURATION }} = "30" ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ],
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
                'expectedStatements' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.duration-selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ DURATION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.duration-selector\'))',
                    '{{ DURATION }} = {{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ DURATION }}) ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ],
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
                'expectedStatements' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.duration-selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ DURATION }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.duration-selector\'))',
                    '{{ DURATION }} = {{ DURATION }}->getAttribute(\'attribute_name\') ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ],
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
                'expectedStatements' => [
                    '{{ WEBDRIVER_DIMENSION }} = {{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ DURATION }} = (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                    . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ],
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
                'expectedStatements' => [
                    '{{ DURATION }} = {{ PANTHER_CLIENT }}->getTitle() ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ],
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
                'expectedStatements' => [
                    '{{ DURATION }} = {{ ENVIRONMENT_VARIABLE_ARRAY }}[\'DURATION\'] ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
                ],
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
