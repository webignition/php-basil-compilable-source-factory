<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\DomElementLocator\ElementLocator;

trait CreateFromSetActionDataProviderTrait
{
    public function createFromSetActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to "value"'
                ),
                'expectedLines' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, element identifier, element value' => [
                'action' => new InputAction(
                    'set ".selector" to ".source"',
                    new DomIdentifier('.selector'),
                    new DomIdentifierValue(
                        new DomIdentifier('.source')
                    ),
                    '".selector" to ".source"'
                ),
                'expectedLines' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.source\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ VALUE }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.source\'))',
                    '{{ VALUE }} = {{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ VALUE }}) ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, element identifier, attribute value' => [
                'action' => new InputAction(
                    'set ".selector" to ".source".attribute_name',
                    new DomIdentifier('.selector'),
                    new DomIdentifierValue(
                        (new DomIdentifier('.source'))->withAttributeName('attribute_name')
                    ),
                    '".selector" to ".source"'
                ),
                'expectedLines' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.source\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ VALUE }} = {{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.source\'))',
                    '{{ VALUE }} = {{ VALUE }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, browser property' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $browser.size'
                ),
                'expectedLines' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ WEBDRIVER_DIMENSION }} = {{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ VALUE }} = (string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                    . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'COLLECTION',
                        'WEBDRIVER_DIMENSION',
                        'VALUE',
                    ])),
            ],
            'input action, page property' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $page.url'
                ),
                'expectedLines' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ PANTHER_CLIENT }}->getCurrentURL() ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                 ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::PANTHER_CLIENT,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
            'input action, environment value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $env.KEY'
                ),
                'expectedLines' => [
                    '{{ HAS }} = {{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENVIRONMENT_VARIABLE_ARRAY }}[\'KEY\'] ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ]))
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                        VariableNames::WEBDRIVER_ELEMENT_MUTATOR,
                    ]))
                    ->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'HAS',
                        'COLLECTION',
                        'VALUE',
                    ])),
            ],
        ];
    }
}
