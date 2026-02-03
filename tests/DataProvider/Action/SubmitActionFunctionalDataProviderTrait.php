<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Tests\Model\StatementHandlerTestData;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilModels\Parser\ActionParser;

trait SubmitActionFunctionalDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function submitActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $fixture = '/action-click-submit.html';

        $setupStatements = new Body(
            new BodyContentCollection()
                ->append(
                    StatementFactory::createAssertBrowserTitle('Click'),
                )
        );

        $teardownStatements = new Body(
            new BodyContentCollection()
                ->append(
                    StatementFactory::createAssertBrowserTitle('Form'),
                )
        );

        return [
            'interaction action (submit), form submit button' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('submit $"#form input[type=\'submit\']"', 0),
                )
                    ->withBeforeTest($setupStatements)
                    ->withAfterTest($teardownStatements),
            ],
            'interaction action (submit), form' => [
                'data' => new StatementHandlerTestData(
                    $fixture,
                    $actionParser->parse('submit $"#form"', 0),
                )
                    ->withBeforeTest($setupStatements)
                    ->withAfterTest($teardownStatements),
            ],
        ];
    }
}
