<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilModels\Parser\ActionParser;

trait ClickActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function clickActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $submitButtonVariable = Property::asObjectVariable('submitButton');

        $fixture = '/action-click-submit.html';

        return [
            'interaction action (click), link' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('click $"#link-to-index"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createAssertBrowserTitle('Click'),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                        )
                )),
            ],
            'interaction action (click), submit button' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('click $"#form input[type=\'submit\']"', 0),
                )->withBeforeTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createAssertBrowserTitle('Click'),
                        )
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '#form input[type="submit"]',
                                $submitButtonVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame(
                                '"false"',
                                '$submitButton->getAttribute(\'data-clicked\')'
                            ),
                        )
                ))->withAfterTest(new Body(
                    new BodyContentCollection()
                        ->append(
                            StatementFactory::createCrawlerFilterCallForElement(
                                '#form input[type="submit"]',
                                $submitButtonVariable
                            ),
                        )
                        ->append(
                            StatementFactory::createAssertSame(
                                '"true"',
                                '$submitButton->getAttribute(\'data-clicked\')'
                            ),
                        )
                )),
            ]
        ];
    }
}
