<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Property;
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

        return [
            'interaction action (click), link' => [
                'fixture' => '/action-click-submit.html',
                'statement' => $actionParser->parse('click $"#link-to-index"', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Click'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'statement' => $actionParser->parse('click $"#form input[type=\'submit\']"', 0),
                'additionalVariableIdentifiers' => [],
                'additionalSetupStatements' => new Body([
                    StatementFactory::createAssertBrowserTitle('Click'),
                    StatementFactory::createCrawlerFilterCallForElement(
                        '#form input[type="submit"]',
                        $submitButtonVariable
                    ),
                    StatementFactory::createAssertSame('"false"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
                'teardownStatements' => new Body([
                    StatementFactory::createCrawlerFilterCallForElement(
                        '#form input[type="submit"]',
                        $submitButtonVariable
                    ),
                    StatementFactory::createAssertSame('"true"', '$submitButton->getAttribute(\'data-clicked\')'),
                ]),
            ],
        ];
    }
}
