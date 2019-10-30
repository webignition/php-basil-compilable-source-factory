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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ COLLECTION }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ VALUE }} = "value" ?? null'),
                    new Statement('{{ VALUE }} = (string) {{ VALUE }}'),
                    new Statement('{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ COLLECTION }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.source\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ VALUE }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.source\'))'),
                    new Statement('{{ VALUE }} = '
                        . '{{ WEBDRIVER_ELEMENT_INSPECTOR }}->getValue({{ VALUE }}) ?? null'),
                    new Statement('{{ VALUE }} = (string) {{ VALUE }}'),
                    new Statement('{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ COLLECTION }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->hasOne(new ElementLocator(\'.source\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ VALUE }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->findOne(new ElementLocator(\'.source\'))'),
                    new Statement('{{ VALUE }} = {{ VALUE }}->getAttribute(\'attribute_name\') ?? null'),
                    new Statement('{{ VALUE }} = (string) {{ VALUE }}'),
                    new Statement('{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ COLLECTION }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ WEBDRIVER_DIMENSION }} = '
                        . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()'),
                    new Statement('{{ VALUE }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null'),
                    new Statement('{{ VALUE }} = (string) {{ VALUE }}'),
                    new Statement('{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement('{{ COLLECTION }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ VALUE }} = {{ PANTHER_CLIENT }}->getCurrentURL() ?? null'),
                    new Statement('{{ VALUE }} = (string) {{ VALUE }}'),
                    new Statement('{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})'),
                ]),
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
                'expectedContent' => new LineList([
                    new Statement('{{ HAS }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->has(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ PHPUNIT_TEST_CASE }}->assertTrue({{ HAS }})'),
                    new Statement( '{{ COLLECTION }} = '
                        . '{{ DOM_CRAWLER_NAVIGATOR }}->find(new ElementLocator(\'.selector\'))'),
                    new Statement('{{ VALUE }} = {{ ENVIRONMENT_VARIABLE_ARRAY }}[\'KEY\'] ?? null'),
                    new Statement('{{ VALUE }} = (string) {{ VALUE }}'),
                    new Statement('{{ WEBDRIVER_ELEMENT_MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})'),
                ]),
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
