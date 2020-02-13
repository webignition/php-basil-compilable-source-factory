<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilParser\ActionParser;

trait SetActionFunctionalDataProviderTrait
{
    public function setActionFunctionalDataProvider(): array
    {
        return [];

//        $actionParser = ActionParser::create();
//
//        $setActionFunctionalVariableIdentifiers = [
//            'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
//            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
//            'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
//        ];
//
//        return array_merge(
//            $this->setActionForTextInputFunctionalDataProvider(),
//            $this->setActionForTextareaFunctionalDataProvider(),
//            $this->setActionForSelectFunctionalDataProvider(),
//            $this->setActionForOptionCollectionFunctionalDataProvider(),
//            $this->setActionForRadioGroupFunctionalDataProvider(),
//            [
//                'input action, element identifier, element value' => [
//                    'fixture' => '/form.html',
//                    'action' => $actionParser->parse(
//                        'set $"input[name=input-without-value]" to $".textarea-non-empty"'
//                    ),
//                    'additionalSetupStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                    ]),
//                    'teardownStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame('"textarea content"', '$input->getAttribute("value")'),
//                    ]),
//                    'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//                ],
//                'input action, element identifier, attribute value' => [
//                    'fixture' => '/form.html',
//                    'action' => $actionParser->parse(
//                        'set $"input[name=input-without-value]" to $"#form1".action'
//                    ),
//                    'additionalSetupStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                    ]),
//                    'teardownStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame(
//                            '"http://127.0.0.1:9080/action1"',
//                            '$input->getAttribute("value")'
//                        ),
//                    ]),
//                    'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//                ],
//                'input action, browser property' => [
//                    'fixture' => '/form.html',
//                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $browser.size'),
//                    'additionalSetupStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                    ]),
//                    'teardownStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame(
//                            '"1200x1100"',
//                            '$input->getAttribute("value")'
//                        ),
//                    ]),
//                    'additionalVariableIdentifiers' => array_merge($setActionFunctionalVariableIdentifiers, [
//                        'WEBDRIVER_DIMENSION' => ResolvedVariableNames::WEBDRIVER_DIMENSION_VARIABLE_NAME,
//                    ]),
//                ],
//                'input action, page property' => [
//                    'fixture' => '/form.html',
//                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $page.url'),
//                    'additionalSetupStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                    ]),
//                    'teardownStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame(
//                            '"http://127.0.0.1:9080/form.html"',
//                            '$input->getAttribute("value")'
//                        ),
//                    ]),
//                    'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//                ],
//                'input action, environment value' => [
//                    'fixture' => '/form.html',
//                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $env.TEST1'),
//                    'additionalSetupStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                    ]),
//                    'teardownStatements' => new CodeBlock([
//                        StatementFactory::createCrawlerFilterCallForElement(
//                            'input[name=input-without-value]',
//                            '$input'
//                        ),
//                        StatementFactory::createAssertSame(
//                            '"environment value"',
//                            '$input->getAttribute("value")'
//                        ),
//                    ]),
//                    'additionalVariableIdentifiers' => array_merge($setActionFunctionalVariableIdentifiers, [
//                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
//                    ]),
//                ],
//            ]
//        );
    }

//    private function setActionForTextInputFunctionalDataProvider(): array
//    {
//        $actionParser = ActionParser::create();
//
//        $setActionFunctionalVariableIdentifiers = [
//            'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
//            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
//            'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
//        ];
//
//        return [
//            'input action, literal value: empty text input, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=input-without-value]" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
//                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
//                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: empty text input, non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=input-without-value]" to "non-empty value"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
//                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-without-value]', '$input'),
//                    StatementFactory::createAssertSame('"non-empty value"', '$input->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: non-empty text input, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=input-with-value]" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
//                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
//                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: non-empty text input, non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=input-with-value]" to "new value"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
//                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('input[name=input-with-value]', '$input'),
//                    StatementFactory::createAssertSame('"new value"', '$input->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//        ];
//    }
//
//    private function setActionForTextareaFunctionalDataProvider(): array
//    {
//        $actionParser = ActionParser::create();
//
//        $setActionFunctionalVariableIdentifiers = [
//            'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
//            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
//            'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
//        ];
//
//        return [
//            'input action, literal value: empty textarea, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".textarea-empty" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
//                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
//                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: empty textarea, non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".textarea-empty" to "non-empty value"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
//                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', '$textarea'),
//                    StatementFactory::createAssertSame('"non-empty value"', '$textarea->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: non-empty textarea, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".textarea-non-empty" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
//                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
//                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: non-empty textarea, non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".textarea-non-empty" to "new value"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
//                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', '$textarea'),
//                    StatementFactory::createAssertSame('"new value"', '$textarea->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//        ];
//    }
//
//    private function setActionForSelectFunctionalDataProvider(): array
//    {
//        $actionParser = ActionParser::create();
//
//        $setActionFunctionalVariableIdentifiers = [
//            'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
//            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
//            'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
//        ];
//
//        return [
//            'input action, literal value: select none selected, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-none-selected" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: select none selected, invalid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-none-selected" to "invalid"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: select none selected, valid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-none-selected" to "none-selected-2"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: select has selected, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-has-selected" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: select has selected, invalid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-has-selected" to "invalid"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: select has selected, valid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-has-selected" to "has-selected-3"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//        ];
//    }
//
//    private function setActionForOptionCollectionFunctionalDataProvider(): array
//    {
//        $actionParser = ActionParser::create();
//
//        $setActionFunctionalVariableIdentifiers = [
//            'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
//            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
//            'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
//        ];
//
//        return [
//            'input action, literal value: option group none selected, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-none-selected option" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: option group none selected, invalid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-none-selected option" to "invalid"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: option group none selected, valid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-none-selected option" to "none-selected-2"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', '$select'),
//                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: option group has selected, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-has-selected option" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: option group has selected, invalid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-has-selected option" to "invalid"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: option group has selected, valid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $".select-has-selected option" to "has-selected-3"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', '$select'),
//                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//        ];
//    }
//
//    private function setActionForRadioGroupFunctionalDataProvider(): array
//    {
//        $actionParser = ActionParser::create();
//
//        $setActionFunctionalVariableIdentifiers = [
//            'COLLECTION' => ResolvedVariableNames::COLLECTION_VARIABLE_NAME,
//            'HAS' => ResolvedVariableNames::HAS_VARIABLE_NAME,
//            'VALUE' => ResolvedVariableNames::VALUE_VARIABLE_NAME,
//        ];
//
//        return [
//            'input action, literal value: radio group none checked, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: radio group none checked, invalid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to "invalid"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: radio group none checked, valid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to "not-checked-2"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: radio group has checked, empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=radio-checked]" to ""'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: radio group has checked, invalid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=radio-checked]" to "invalid"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//            'input action, literal value: radio group has checked, valid non-empty value' => [
//                'fixture' => '/form.html',
//                'action' => $actionParser->parse('set $"input[name=radio-checked]" to "checked-3"'),
//                'additionalSetupStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'teardownStatements' => new CodeBlock([
//                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', '$radioGroup'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
//                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
//                    StatementFactory::createAssertTrue('$radioGroup->getElement(2)->isSelected()'),
//                ]),
//                'additionalVariableIdentifiers' => $setActionFunctionalVariableIdentifiers,
//            ],
//        ];
//    }
}
