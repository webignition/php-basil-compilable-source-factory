<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

trait SetActionFunctionalDataProviderTrait
{
    private $setActionFunctionalFixture = '/form.html';

    private $setActionFunctionalSetupStatements = [
        '$navigator = Navigator::create($crawler);',
        '$mutator = Mutator::create();',
    ];

    private $setActionFunctionalVariableIdentifiers = [
        'COLLECTION' => self::COLLECTION_VARIABLE_NAME,
        'HAS' => self::HAS_VARIABLE_NAME,
        'VALUE' => self::VALUE_VARIABLE_NAME,
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
        VariableNames::WEBDRIVER_ELEMENT_MUTATOR => '$mutator',
    ];

    private function createSetActionFunctionalMetadata(): MetadataInterface
    {
        return (new Metadata())
            ->withAdditionalClassDependencies(new ClassDependencyCollection([
                new ClassDependency(Mutator::class),
                new ClassDependency(Navigator::class),
            ]));
    }

    public function setActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return array_merge(
            $this->setActionForTextInputFunctionalDataProvider(),
            $this->setActionForTextareaFunctionalDataProvider(),
            $this->setActionForSelectFunctionalDataProvider(),
            $this->setActionForOptionCollectionFunctionalDataProvider(),
            $this->setActionForRadioGroupFunctionalDataProvider(),
            [
                'input action, element identifier, element value' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => new InputAction(
                        'set "input[name=input-without-value]" to $elements.textarea',
                        new DomIdentifier('input[name=input-without-value]'),
                        DomIdentifierValue::create('.textarea-non-empty'),
                        '"input[name=input-without-value]" to $elements.textarea'
                    ),
                    'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                        '$inspector = Inspector::create();',
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ]),
                    'teardownStatements' => [
                        '$this->assertEquals("textarea content", $input->getAttribute("value"));',
                    ],
                    'additionalVariableIdentifiers' => array_merge($this->setActionFunctionalVariableIdentifiers, [
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                    ]),
                    'metadata' => $this->createSetActionFunctionalMetadata()
                        ->withAdditionalClassDependencies(new ClassDependencyCollection([
                            new ClassDependency(Inspector::class),
                        ])),
                ],
                'input action, element identifier, attribute value' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => new InputAction(
                        'set "input[name=input-without-value]" to $elements.form.action',
                        new DomIdentifier('input[name=input-without-value]'),
                        new DomIdentifierValue(
                            (new DomIdentifier('#form1'))->withAttributeName('action')
                        ),
                        '"input[name=input-without-value]" to $elements.form.action'
                    ),
                    'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ]),
                    'teardownStatements' => [
                        '$this->assertEquals("http://127.0.0.1:9080/action1", $input->getAttribute("value"));',
                    ],
                    'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'metadata' => $this->createSetActionFunctionalMetadata(),
                ],
                'input action, browser property' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => $actionFactory->createFromActionString(
                        'set "input[name=input-without-value]" to $browser.size'
                    ),
                    'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ]),
                    'teardownStatements' => [
                        '$this->assertEquals("1200x1100", $input->getAttribute("value"));',
                    ],
                    'additionalVariableIdentifiers' => array_merge($this->setActionFunctionalVariableIdentifiers, [
                        'WEBDRIVER_DIMENSION' => self::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    ]),
                    'metadata' => $this->createSetActionFunctionalMetadata(),
                ],
                'input action, page property' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => $actionFactory->createFromActionString(
                        'set "input[name=input-without-value]" to $page.url'
                    ),
                    'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ]),
                    'teardownStatements' => [
                        '$this->assertEquals("http://127.0.0.1:9080/form.html", $input->getAttribute("value"));',
                    ],
                    'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                    'metadata' => $this->createSetActionFunctionalMetadata(),
                ],
                'input action, environment value' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => $actionFactory->createFromActionString(
                        'set "input[name=input-without-value]" to $env.TEST1'
                    ),
                    'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                        '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                        '$this->assertEquals("", $input->getAttribute("value"));',
                    ]),
                    'teardownStatements' => [
                        '$this->assertEquals("environment value", $input->getAttribute("value"));',
                    ],
                    'additionalVariableIdentifiers' => array_merge($this->setActionFunctionalVariableIdentifiers, [
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
                    ]),
                    'metadata' => $this->createSetActionFunctionalMetadata(),
                ],
            ]
        );
    }

    private function setActionForTextInputFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: empty text input, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-without-value]" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: empty text input, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-without-value]" to "non-empty value"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$input = $crawler->filter(\'input[name=input-without-value]\')->getElement(0);',
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("non-empty value", $input->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: non-empty text input, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-with-value]" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$input = $crawler->filter(\'input[name=input-with-value]\')->getElement(0);',
                    '$this->assertEquals("test", $input->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("", $input->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: non-empty text input, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=input-with-value]" to "new value"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$input = $crawler->filter(\'input[name=input-with-value]\')->getElement(0);',
                    '$this->assertEquals("test", $input->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("new value", $input->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
        ];
    }

    private function setActionForTextareaFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: empty textarea, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-empty" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$textarea = $crawler->filter(\'.textarea-empty\')->getElement(0);',
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: empty textarea, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-empty" to "non-empty value"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$textarea = $crawler->filter(\'.textarea-empty\')->getElement(0);',
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("non-empty value", $textarea->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: non-empty textarea, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-non-empty" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$textarea = $crawler->filter(\'.textarea-non-empty\')->getElement(0);',
                    '$this->assertEquals("textarea content", $textarea->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("", $textarea->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: non-empty textarea, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".textarea-non-empty" to "new value"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$textarea = $crawler->filter(\'.textarea-non-empty\')->getElement(0);',
                    '$this->assertEquals("textarea content", $textarea->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("new value", $textarea->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
        ];
    }

    private function setActionForSelectFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: select none selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: select none selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected" to "invalid"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: select none selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected" to "none-selected-2"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("none-selected-2", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: select has selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: select has selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected" to "invalid"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: select has selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected" to "has-selected-3"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("has-selected-3", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
        ];
    }

    private function setActionForOptionCollectionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: option group none selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected option" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: option group none selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected option" to "invalid"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: option group none selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-none-selected option" to "none-selected-2"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-none-selected\')->getElement(0);',
                    '$this->assertEquals("none-selected-1", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("none-selected-2", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: option group has selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected option" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: option group has selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected option" to "invalid"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: option group has selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set ".select-has-selected option" to "has-selected-3"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$select = $crawler->filter(\'.select-has-selected\')->getElement(0);',
                    '$this->assertEquals("has-selected-2", $select->getAttribute("value"));',
                ]),
                'teardownStatements' => [
                    '$this->assertEquals("has-selected-3", $select->getAttribute("value"));',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
        ];
    }

    private function setActionForRadioGroupFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, literal value: radio group none checked, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-not-checked]" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$radioGroup = $crawler->filter(\'input[name=radio-not-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ]),
                'teardownStatements' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: radio group none checked, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-not-checked]" to "invalid"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$radioGroup = $crawler->filter(\'input[name=radio-not-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ]),
                'teardownStatements' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: radio group none checked, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-not-checked]" to "not-checked-2"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$radioGroup = $crawler->filter(\'input[name=radio-not-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ]),
                'teardownStatements' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: radio group has checked, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-checked]" to ""'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$radioGroup = $crawler->filter(\'input[name=radio-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ]),
                'teardownStatements' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: radio group has checked, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-checked]" to "invalid"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$radioGroup = $crawler->filter(\'input[name=radio-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ]),
                'teardownStatements' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
            'input action, literal value: radio group has checked, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionFactory->createFromActionString(
                    'set "input[name=radio-checked]" to "checked-3"'
                ),
                'additionalSetupStatements' => array_merge($this->setActionFunctionalSetupStatements, [
                    '$radioGroup = $crawler->filter(\'input[name=radio-checked]\');',
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(1)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(2)->isSelected());',
                ]),
                'teardownStatements' => [
                    '$this->assertFalse($radioGroup->getElement(0)->isSelected());',
                    '$this->assertFalse($radioGroup->getElement(1)->isSelected());',
                    '$this->assertTrue($radioGroup->getElement(2)->isSelected());',
                ],
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,

                'metadata' => $this->createSetActionFunctionalMetadata(),
            ],
        ];
    }
}
