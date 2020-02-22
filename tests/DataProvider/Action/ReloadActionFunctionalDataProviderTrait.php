<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;

trait ReloadActionFunctionalDataProviderTrait
{
    public function reloadActionFunctionalDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $setupTeardownStatements = new CodeBlock([
            new Statement(
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'assertCount',
                    [
                        new LiteralExpression('0'),
                        new ObjectMethodInvocation(
                            VariablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                            'filter',
                            [
                                new LiteralExpression('"#hello"')
                            ]
                        ),
                    ]
                )
            ),
            new Statement(
                new MethodInvocation('usleep', [new LiteralExpression('100000')])
            ),
            new Statement(
                new ObjectMethodInvocation(
                    VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'assertCount',
                    [
                        new LiteralExpression('1'),
                        new ObjectMethodInvocation(
                            VariablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                            'filter',
                            [
                                new LiteralExpression('"#hello"')
                            ]
                        ),
                    ]
                )
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
