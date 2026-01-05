<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames as ResolvedNames;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilModels\Parser\ActionParser;

trait SetActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function setActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $inputPlaceholder = new VariableName('input');

        return array_merge(
            self::setActionForTextInputFunctionalDataProvider(),
            self::setActionForTextareaFunctionalDataProvider(),
            self::setActionForSelectFunctionalDataProvider(),
            self::setActionForOptionCollectionFunctionalDataProvider(),
            self::setActionForRadioGroupFunctionalDataProvider(),
            [
                'input action, element identifier, element value' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse(
                        'set $"input[name=input-without-value]" to $".textarea-non-empty"',
                        0,
                    ),
                    'additionalSetupStatements' => new Body([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new Body([
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
                        'set $"input[name=input-without-value]" to $"#form1".action',
                        0,
                    ),
                    'additionalSetupStatements' => new Body([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new Body([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame(
                            '"/action1"',
                            '$input->getAttribute("value")'
                        ),
                    ]),
                ],
                'input action, browser property' => [
                    'fixture' => '/form.html',
                    'action' => $actionParser->parse(
                        'set $"input[name=input-without-value]" to $browser.size',
                        0,
                    ),
                    'additionalSetupStatements' => new Body([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new Body([
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
                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $page.url', 0),
                    'additionalSetupStatements' => new Body([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new Body([
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
                    'action' => $actionParser->parse('set $"input[name=input-without-value]" to $env.TEST1', 0),
                    'additionalSetupStatements' => new Body([
                        StatementFactory::createCrawlerFilterCallForElement(
                            'input[name=input-without-value]',
                            $inputPlaceholder
                        ),
                        StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                    ]),
                    'teardownStatements' => new Body([
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
                        VariableNameEnum::ENVIRONMENT_VARIABLE_ARRAY->value => ResolvedNames::ENV_ARRAY_VARIABLE_NAME,
                    ],
                ],
            ]
        );
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForTextInputFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $inputPlaceholder = new VariableName('input');

        return [
            'input action, literal value: empty text input, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-without-value]" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: empty text input, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-without-value]" to "non-empty value"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-without-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"non-empty value"', '$input->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty text input, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-with-value]" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty text input, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=input-with-value]" to "new value"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        'input[name=input-with-value]',
                        $inputPlaceholder
                    ),
                    StatementFactory::createAssertSame('"new value"', '$input->getAttribute("value")'),
                ]),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForTextareaFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $textareaPlaceholder = new VariableName('textarea');

        return [
            'input action, literal value: empty textarea, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-empty" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: empty textarea, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-empty" to "non-empty value"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"non-empty value"', '$textarea->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty textarea, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-non-empty" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: non-empty textarea, non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".textarea-non-empty" to "new value"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"textarea content"', '$textarea->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.textarea-non-empty', $textareaPlaceholder),
                    StatementFactory::createAssertSame('"new value"', '$textarea->getAttribute("value")'),
                ]),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForSelectFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $selectPlaceholder = new VariableName('select');

        return [
            'input action, literal value: select none selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select none selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected" to "invalid"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select none selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected" to "none-selected-2"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select has selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select has selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected" to "invalid"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: select has selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected" to "has-selected-3"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                ]),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForOptionCollectionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $selectPlaceholder = new VariableName('select');

        return [
            'input action, literal value: option group none selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected option" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group none selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected option" to "invalid"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group none selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-none-selected option" to "none-selected-2"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-none-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group has selected, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected option" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group has selected, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected option" to "invalid"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
            ],
            'input action, literal value: option group has selected, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $".select-has-selected option" to "has-selected-3"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement('.select-has-selected', $selectPlaceholder),
                    StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                ]),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForRadioGroupFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $radioGroupPlaceholder = new VariableName('radioGroup');

        return [
            'input action, literal value: radio group none checked, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group none checked, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to "invalid"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group none checked, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-not-checked]" to "not-checked-2"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-not-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group has checked, empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-checked]" to ""', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group has checked, invalid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-checked]" to "invalid"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
            'input action, literal value: radio group has checked, valid non-empty value' => [
                'fixture' => '/form.html',
                'action' => $actionParser->parse('set $"input[name=radio-checked]" to "checked-3"', 0),
                'additionalSetupStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupPlaceholder),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                    StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                    StatementFactory::createAssertTrue('$radioGroup->getElement(2)->isSelected()'),
                ]),
            ],
        ];
    }
}
