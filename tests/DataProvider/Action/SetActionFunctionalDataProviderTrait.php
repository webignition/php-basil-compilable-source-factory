<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilActionGenerator\ActionGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;

trait SetActionFunctionalDataProviderTrait
{
    private $setActionFunctionalFixture = '/form.html';

    private $setActionFunctionalVariableIdentifiers = [
        'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
        'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
        'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
    ];

    public function setActionFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

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
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame('"textarea content"', '$input->getAttribute("value")'),
                    ]),
                    'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
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
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame(
                            '"http://127.0.0.1:9080/action1"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                    'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                ],
                'input action, browser property' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => $actionGenerator->generate(
                        'set "input[name=input-without-value]" to $browser.size'
                    ),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame(
                            '"1200x1100"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                    'additionalVariableIdentifiers' => array_merge($this->setActionFunctionalVariableIdentifiers, [
                        'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
                    ]),
                ],
                'input action, page property' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => $actionGenerator->generate(
                        'set "input[name=input-without-value]" to $page.url'
                    ),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame(
                            '"http://127.0.0.1:9080/form.html"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                    'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
                ],
                'input action, environment value' => [
                    'fixture' => $this->setActionFunctionalFixture,
                    'action' => $actionGenerator->generate(
                        'set "input[name=input-without-value]" to $env.TEST1'
                    ),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            '$input'
                        ),
                        StatementFactory::createAssertSame(
                            '"environment value"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                    'additionalVariableIdentifiers' => array_merge($this->setActionFunctionalVariableIdentifiers, [
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                    ]),
                ],
            ]
        );
    }

    private function setActionForTextInputFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'input action, literal value: empty text input, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=input-without-value]" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: empty text input, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=input-without-value]" to "non-empty value"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
                    StatementFactory::createAssertSame('"non-empty value"', '$input->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: non-empty text input, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=input-with-value]" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: non-empty text input, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=input-with-value]" to "new value"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
                    StatementFactory::createAssertSame('"new value"', '$input->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
        ];
    }

    private function setActionForTextareaFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'input action, literal value: empty textarea, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".textarea-empty" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: empty textarea, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".textarea-empty" to "non-empty value"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
                    StatementFactory::createAssertSame('"non-empty value"', '$textarea->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: non-empty textarea, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".textarea-non-empty" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: non-empty textarea, non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".textarea-non-empty" to "new value"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
                    StatementFactory::createAssertSame('"new value"', '$textarea->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
        ];
    }

    private function setActionForSelectFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'input action, literal value: select none selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-none-selected" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: select none selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-none-selected" to "invalid"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: select none selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-none-selected" to "none-selected-2"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: select has selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-has-selected" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: select has selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-has-selected" to "invalid"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: select has selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-has-selected" to "has-selected-3"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
        ];
    }

    private function setActionForOptionCollectionFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'input action, literal value: option group none selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-none-selected option" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: option group none selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-none-selected option" to "invalid"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: option group none selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-none-selected option" to "none-selected-2"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: option group has selected, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-has-selected option" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: option group has selected, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-has-selected option" to "invalid"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: option group has selected, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set ".select-has-selected option" to "has-selected-3"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
        ];
    }

    private function setActionForRadioGroupFunctionalDataProvider(): array
    {
        $actionGenerator = ActionGenerator::createGenerator();

        return [
            'input action, literal value: radio group none checked, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=radio-not-checked]" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: radio group none checked, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=radio-not-checked]" to "invalid"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: radio group none checked, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=radio-not-checked]" to "not-checked-2"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: radio group has checked, empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=radio-checked]" to ""'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: radio group has checked, invalid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=radio-checked]" to "invalid"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
            'input action, literal value: radio group has checked, valid non-empty value' => [
                'fixture' => $this->setActionFunctionalFixture,
                'action' => $actionGenerator->generate(
                    'set "input[name=radio-checked]" to "checked-3"'
                ),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'additionalVariableIdentifiers' => $this->setActionFunctionalVariableIdentifiers,
            ],
        ];
    }
}
