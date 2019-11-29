<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\PlaceholderFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilParser\ActionParser;

trait ReloadActionFunctionalDataProviderTrait
{
    public function reloadActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $setupTeardownStatements = new CodeBlock([
            StatementFactory::create(
                '%s->assertCount(0, %s->filter("#hello"))',
                [
                    PlaceholderFactory::phpUnitTestCase(),
                    PlaceholderFactory::pantherCrawler(),
                ]
            ),
            new Statement('usleep(100000)'),
            StatementFactory::create(
                '%s->assertCount(1, %s->filter("#hello"))',
                [
                    PlaceholderFactory::phpUnitTestCase(),
                    PlaceholderFactory::pantherCrawler(),
                ]
            ),
        ]);

        return [
            'reload action' => [
                'fixture' => '/action-wait-for.html',
                'action' => $actionParser->parse('reload'),
                'additionalSetupStatements' => $setupTeardownStatements,
                'teardownStatements' => $setupTeardownStatements,
            ],
        ];
    }
}
