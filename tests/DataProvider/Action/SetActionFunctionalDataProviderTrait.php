<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\ResolvingPlaceholder;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait SetActionFunctionalDataProviderTrait
{
    public function setActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $inputPlaceholder = new ResolvingPlaceholder('input');

        return array_merge(
            $this->setActionForTextInputFunctionalDataProvider(),
            $this->setActionForTextareaFunctionalDataProvider(),
            $this->setActionForSelectFunctionalDataProvider(),
            $this->setActionForOptionCollectionFunctionalDataProvider(),
            $this->setActionForRadioGroupFunctionalDataProvider(),
            [
                'input action, element identifier, element value' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse(
                        'set $"input[name=input-without-value]" to $".textarea-non-empty"'
                    ),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('"textarea content"', '$input->getAttribute("value")'),
                    ]),
                ],
                'input action, element identifier, attribute value' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse(
                        'set $"input[name=input-without-value]" to $"#form1".action'
                    ),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame(
                            '"http://127.0.0.1:9080/action1"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                ],
                'input action, browser property' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $browser.size'),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame(
                            '"1200x1100"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                ],
                'input action, page property' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $page.url'),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame(
                            '"http://127.0.0.1:9080/form.html"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                ],
                'input action, environment value' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $env.TEST1'),
                    'additionalSetupStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new CodeBlock([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame(
                            '"environment value"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                    'additionalVariableIdentifiers' => [
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY => ResolvedVariableNames::ENV_ARRAY_VARIABLE_NAME,
                    ],
                ],
            ]
        );
    }

    private function setActionForTextInputFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $inputPlaceholder = new ResolvingPlaceholder('input');

        return [
            'input action, literal value: empty text input, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-without-value]" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: empty text input, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-without-value]" to "non-empty value"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"non-empty value"', '$input->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty text input, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-with-value]" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty text input, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-with-value]" to "new value"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"new value"', '$input->getAttribute("value")'),
                ]),
            ],
        ];
    }

    private function setActionForTextareaFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $textareaPlaceholder = new ResolvingPlaceholder('textarea');

        return [
            'input action, literal value: empty textarea, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-empty" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: empty textarea, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-empty" to "non-empty value"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"non-empty value"', '$textarea->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty textarea, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-non-empty" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty textarea, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-non-empty" to "new value"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"new value"', '$textarea->getAttribute("value")'),
                ]),
            ],
        ];
    }

    private function setActionForSelectFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $selectPlaceholder = new ResolvingPlaceholder('select');

        return [
            'input action, literal value: select none selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select none selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected" to "invalid"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select none selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected" to "none-selected-2"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select has selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select has selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected" to "invalid"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select has selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected" to "has-selected-3"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                ]),
            ],
        ];
    }

    private function setActionForOptionCollectionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $selectPlaceholder = new ResolvingPlaceholder('select');

        return [
            'input action, literal value: option group none selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected option" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group none selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected option" to "invalid"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group none selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected option" to "none-selected-2"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group has selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected option" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group has selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected option" to "invalid"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
             ],
            'input action, literal value: option group has selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected option" to "has-selected-3"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                ]),
            ],
        ];
    }

    private function setActionForRadioGroupFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $radioGroupPlaceholder = new ResolvingPlaceholder('radioGroup');

        return [
            'input action, literal value: radio group none checked, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group none checked, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to "invalid"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group none checked, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to "not-checked-2"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group has checked, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-checked]" to ""'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group has checked, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-checked]" to "invalid"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group has checked, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-checked]" to "checked-3"'),
                'additionalSetupStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new CodeBlock([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
        ];
    }
}
