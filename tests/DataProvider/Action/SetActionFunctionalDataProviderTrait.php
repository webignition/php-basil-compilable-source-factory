<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
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
        $inputVariable = Property::asObjectVariable('input');

        $fixture = '/form.html';

        return array_merge(
            self::setActionForTextInputFunctionalDataProvider(),
            self::setActionForTextareaFunctionalDataProvider(),
            self::setActionForSelectFunctionalDataProvider(),
            self::setActionForOptionCollectionFunctionalDataProvider(),
            self::setActionForRadioGroupFunctionalDataProvider(),
            [
                'input action, element identifier, element value' => [
                    'data' => new StatementHandlerTestData(
                        $fixture,
                        $actionParser->parse(
                            'set $"input[name=input-without-value]" to $".textarea-non-empty"',
                            0,
                        )
                    )->withBeforeTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                            )
                    ))->withAfterTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame(
                                    '"textarea content"',
                                    '$input->getAttribute("value")'
                                ),
                            )
                    )),
                ],
                'input action, element identifier, attribute value' => [
                    'data' => new StatementHandlerTestData(
                        $fixture,
                        $actionParser->parse(
                            'set $"input[name=input-without-value]" to $"#form1".action',
                            0,
                        ),
                    )->withBeforeTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                            )
                    ))->withAfterTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame(
                                    '"/action1"',
                                    '$input->getAttribute("value")'
                                ),
                            )
                    )),
                ],
                'input action, browser property' => [
                    'data' => new StatementHandlerTestData(
                        $fixture,
                        $actionParser->parse(
                            'set $"input[name=input-without-value]" to $browser.size',
                            0,
                        ),
                    )->withBeforeTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                            )
                    ))->withAfterTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame(
                                    '"1200x1100"',
                                    '$input->getAttribute("value")'
                                ),
                            )
                    )),
                ],
                'input action, page property' => [
                    'data' => new StatementHandlerTestData(
                        $fixture,
                        $actionParser->parse('set $"input[name=input-without-value]" to $page.url', 0),
                    )->withBeforeTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                            )
                    ))->withAfterTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame(
                                    '"http://127.0.0.1:9080/form.html"',
                                    '$input->getAttribute("value")'
                                ),
                            )
                    )),
                ],
                'input action, environment value' => [
                    'data' => new StatementHandlerTestData(
                        $fixture,
                        $actionParser->parse('set $"input[name=input-without-value]" to $env.TEST1', 0),
                    )->withBeforeTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                            )
                    ))->withAfterTest(new Body(
                        new BodyContentCollection()
                            ->append(
                                StatementFactory::createCrawlerFilterCallForElement(
                                    'input[name=input-without-value]',
                                    $inputVariable
                                ),
                            )
                            ->append(
                                StatementFactory::createAssertSame(
                                    '"environment value"',
                                    '$input->getAttribute("value")'
                                ),
                            )
                    )),
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
        $inputVariable = Property::asObjectVariable('input');

        $fixture = '/form.html';

        return [
            'input action, literal value: empty text input, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=input-without-value]" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-without-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-without-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: empty text input, non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=input-without-value]" to "non-empty value"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-without-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-without-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"non-empty value"', '$input->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: non-empty text input, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=input-with-value]" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-with-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-with-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$input->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: non-empty text input, non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=input-with-value]" to "new value"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-with-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"test"', '$input->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                'input[name=input-with-value]',
                                $inputVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"new value"', '$input->getAttribute("value")'),
                        )
                )),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForTextareaFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $textareaVariable = Property::asObjectVariable('textarea');

        $fixture = '/form.html';

        return [
            'input action, literal value: empty textarea, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".textarea-empty" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaVariable),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaVariable),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: empty textarea, non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".textarea-empty" to "non-empty value"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaVariable),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement('.textarea-empty', $textareaVariable),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"non-empty value"', '$textarea->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: non-empty textarea, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".textarea-non-empty" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.textarea-non-empty',
                                $textareaVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame(
                                '"textarea content"',
                                '$textarea->getAttribute("value")'
                            ),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.textarea-non-empty',
                                $textareaVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('""', '$textarea->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: non-empty textarea, non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".textarea-non-empty" to "new value"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.textarea-non-empty',
                                $textareaVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame(
                                '"textarea content"',
                                '$textarea->getAttribute("value")'
                            ),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.textarea-non-empty',
                                $textareaVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"new value"', '$textarea->getAttribute("value")'),
                        )
                )),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForSelectFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $selectVariable = Property::asObjectVariable('select');

        $fixture = '/form.html';

        return [
            'input action, literal value: select none selected, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-none-selected" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: select none selected, invalid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-none-selected" to "invalid"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: select none selected, valid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-none-selected" to "none-selected-2"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: select has selected, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-has-selected" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: select has selected, invalid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-has-selected" to "invalid"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: select has selected, valid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-has-selected" to "has-selected-3"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                        )
                )),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForOptionCollectionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $selectVariable = Property::asObjectVariable('select');

        $fixture = '/form.html';

        return [
            'input action, literal value: option group none selected, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-none-selected option" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: option group none selected, invalid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-none-selected option" to "invalid"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: option group none selected, valid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-none-selected option" to "none-selected-2"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-1"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-none-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"none-selected-2"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: option group has selected, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-has-selected option" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: option group has selected, invalid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-has-selected option" to "invalid"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                )),
            ],
            'input action, literal value: option group has selected, valid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $".select-has-selected option" to "has-selected-3"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-2"', '$select->getAttribute("value")'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '.select-has-selected',
                                $selectVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame('"has-selected-3"', '$select->getAttribute("value")'),
                        )
                )),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function setActionForRadioGroupFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $radioGroupVariable = Property::asObjectVariable('radioGroup');

        $fixture = '/form.html';

        return [
            'input action, literal value: radio group none checked, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=radio-not-checked]" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall(
                                'input[name=radio-not-checked]',
                                $radioGroupVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall(
                                'input[name=radio-not-checked]',
                                $radioGroupVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                )),
            ],
            'input action, literal value: radio group none checked, invalid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=radio-not-checked]" to "invalid"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall(
                                'input[name=radio-not-checked]',
                                $radioGroupVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall(
                                'input[name=radio-not-checked]',
                                $radioGroupVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                )),
            ],
            'input action, literal value: radio group none checked, valid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=radio-not-checked]" to "not-checked-2"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall(
                                'input[name=radio-not-checked]',
                                $radioGroupVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall(
                                'input[name=radio-not-checked]',
                                $radioGroupVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                )),
            ],
            'input action, literal value: radio group has checked, empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=radio-checked]" to ""', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupVariable),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupVariable),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                )),
            ],
            'input action, literal value: radio group has checked, invalid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=radio-checked]" to "invalid"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupVariable),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupVariable),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                )),
            ],
            'input action, literal value: radio group has checked, valid non-empty value' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('set $"input[name=radio-checked]" to "checked-3"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupVariable),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(2)->isSelected()'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCall('input[name=radio-checked]', $radioGroupVariable),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(0)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertFalse('$radioGroup->getElement(1)->isSelected()'),
                        )
                        ->append(
                            StatementFactory::createAssertTrue('$radioGroup->getElement(2)->isSelected()'),
                        )
                )),
            ],
        ];
    }
}
