<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.source\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ VALUE }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.source\'))',
                    '{{ VALUE }} = {{ INSPECTOR }}->getValue({{ VALUE }}) ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ HAS }} = {{ NAVIGATOR }}->hasOne(new ElementLocator(\'.source\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ VALUE }} = {{ NAVIGATOR }}->findOne(new ElementLocator(\'.source\'))',
                    '{{ VALUE }} = {{ VALUE }}->getAttribute(\'attribute_name\') ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '{{ VALUE }} = '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight() ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ CLIENT }}->getCurrentURL() ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
            'input action, environment value with default' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $env.KEY|"default"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default\'',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
            'input action, environment value with default with whitespace' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $env.KEY|"default value"'
                ),
                'expectedContent' => CodeBlock::fromContent([
                    '{{ HAS }} = {{ NAVIGATOR }}->has(new ElementLocator(\'.selector\'))',
                    '{{ PHPUNIT }}->assertTrue({{ HAS }})',
                    '{{ COLLECTION }} = {{ NAVIGATOR }}->find(new ElementLocator(\'.selector\'))',
                    '{{ VALUE }} = {{ ENV }}[\'KEY\'] ?? \'default value\'',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                    '{{ MUTATOR }}->setValue({{ COLLECTION }}, {{ VALUE }})',
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
