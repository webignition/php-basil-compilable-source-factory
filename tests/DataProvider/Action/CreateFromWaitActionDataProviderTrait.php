<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
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
                'expectedContent' => Block::fromContent([
                    '{{ DURATION }} = "30" ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
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
                'expectedContent' => Block::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.duration-selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ DURATION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.duration-selector\'))',
                    '{{ DURATION }} = {{ INSPECTOR }}->getValue({{ DURATION }}) ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
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
                'expectedContent' => Block::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.duration-selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ DURATION }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.duration-selector\'))',
                    '{{ DURATION }} = {{ DURATION }}->getAttribute(\'attribute_name\') ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
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
                'expectedContent' => Block::fromContent([
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ DURATION }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
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
                'expectedContent' => Block::fromContent([
                    '{{ DURATION }} = {{ CLIENT }}->getTitle() ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
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
                'expectedContent' => Block::fromContent([
                    '{{ DURATION }} = {{ ENV }}[\'DURATION\'] ?? 0',
                    '{{ DURATION }} = (int) {{ DURATION }}',
                    'usleep({{ DURATION }} * 1000)',
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
